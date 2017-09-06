<?php

/**
 * Wet Antispam for Social Engine
 *
 * @category   Application_Custom
 * @package    Wetantispam
 * @copyright  Copyright 2015 Robert Wetzlmayr
 */

/*
Copyright (C) 2015 Robert Wetzlmayr

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software Foundation,
Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301  USA
*/

class Wetantispam_Plugin_Antispam
{
    protected $_log;

    protected $akismet;
    protected $content;
    protected $facts;
    protected $trustlevel;

    public function __construct()
    {
        $this->akismet = new Wetantispam_Service_Akismet();
        $this->facts = new Wetantispam_Plugin_Antispam_Facts();
        $this->trustlevel = Engine_Api::_()->getApi('settings', 'core')->wetantispam_trustlevel;
    }

    /**
     * Check a contribution for spam.
     * Handles the 'onForumTopicCreateBefore', 'onForumPostCreateBefore', 'onMessagesMessageCreateBefore', 'onBlogCreateBefore', 'onCoreCommentCreateBefore' events.
     *
     * @param $event Engine_Hooks_Event
     */
    public function onForumTopicCreateBefore($event)
    {
        $this->facts->content = $event->getPayload()->description;
        $this->facts->resource = __METHOD__;
        $this->facts->itemcount = $this->getItemCount('posts', 'forum', 'user_id');
        return $this->isSpam();
    }

    public function onForumPostCreateBefore($event)
    {
        $this->facts->content = $event->getPayload()->body;
        $this->facts->resource = __METHOD__;
        $this->facts->itemcount = $this->getItemCount('posts', 'forum', 'user_id');
        return $this->isSpam();
    }

    public function onForumPostUpdateBefore($event)
    {
        $this->facts->content = $event->getPayload()->body;
        $this->facts->resource = __METHOD__;
        $this->facts->itemcount = $this->getItemCount('posts', 'forum', 'user_id');
        return $this->isSpam();
    }

    public function onMessagesMessageCreateBefore($event)
    {
        $this->facts->content = $event->getPayload()->body;
        $this->facts->resource = __METHOD__;
        $this->facts->itemcount = $this->getItemCount('messages', 'messages', 'user_id');
        return $this->isSpam();
    }

    public function onBlogCreateBefore($event)
    {
        $this->facts->content = $event->getPayload()->body;
        $this->facts->resource = __METHOD__;
        $this->facts->itemcount = $this->getItemCount('blogs', 'blog', 'owner_id');
        return $this->isSpam();
    }

    public function onBlogUpdateBefore($event)
    {
        $this->facts->content = $event->getPayload()->body;
        $this->facts->resource = __METHOD__;
        $this->facts->itemcount = $this->getItemCount('blogs', 'blog', 'owner_id');
        return $this->isSpam();
    }

    public function onCoreCommentCreateBefore($event)
    {
        $this->facts->content = $event->getPayload()->body;
        $this->facts->resource = __METHOD__;
        $this->facts->itemcount = $this->getItemCount('comments', 'core', 'poster_id');
        return $this->isSpam();
    }

    /**
     * @throws Exception
     * @throws Wetantispam_Plugin_Exception
     */
    private function isSpam()
    {
        $isSpam =
            !$this->isTrustedUser() &&
            $this->akismet->isSpam(array(
            'comment_content' => $this->facts->content,
            // DEBUG: Force false positives
//             'comment_author' => 'viagra-test-123'
        ));

        if ($isSpam) {
            $this->createReport();

            $this->getLog()->log(sprintf(
                "[%s] Content: %s".
                "\r\nItem count: %d.".
                "\r\nUser id: %d.".
                "\r\n\r\n",
                $this->facts->resource,
                $this->facts->content,
                $this->facts->itemcount,
                $this->facts->viewer->getIdentity()
            ), Zend_Log::INFO);

            throw new Wetantispam_Plugin_Exception($this->facts->content);
        }
        return false;
    }

    private function isTrustedUser()
    {
        $trusted = ($this->facts->itemcount >= $this->trustlevel);
        return $trusted;
    }

    private function getItemCount($resource, $module, $owner_id_col)
    {
        $user_id = $this->facts->viewer->getIdentity();
        $table = Engine_Api::_()->getDbtable($resource, $module);
        $select = $table->select();
        $select->where("$owner_id_col = ?", $user_id);
        $paginator = Zend_Paginator::factory($select);
        $count = $paginator->getTotalItemCount();
        return $count;
    }

    /**
     * Create abuse report impersonating the current user reporting herself.
     * Include spam meesage in report.
     *
     * @throws Exception
     */

    private function createReport()
    {
        /** @var $table Core_Model_DbTable_Reports */
        $table = Engine_Api::_()->getItemTable('core_report');
        $db = $table->getAdapter();
        $db->beginTransaction();

        try {
            $report = $table->createRow();
            $report->category = 'spam';
            $report->description = "Spam?\r\n\r\n--\r\n\r\n" . substr(strip_tags($this->facts->content), 0, 1024) . "\r\n";
            $report->subject_type = $this->facts->viewer->getType();
            $report->subject_id = $this->facts->viewer->getIdentity();
            $report->user_id = $this->facts->viewer->getIdentity();
            $report->save();

            // Increment report count
            Engine_Api::_()->getDbtable('statistics', 'core')->increment('core.reports');

            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /**
     * Log an entry into 'wetantispam.log'
     * TODO: Refactor into stand-alone class.
     *
     * @return Zend_Log
     */
    private function getLog()
    {
        if (null === $this->_log) {
            $log = new Zend_Log();
            $log->addWriter(new Zend_Log_Writer_Stream(APPLICATION_PATH . '/temporary/log/wetantispam.log', 'a'));
            if ('development' == APPLICATION_ENV) {
                $log->addWriter(new Zend_Log_Writer_Firebug());
            }
            $this->_log = $log;
        }
        return $this->_log;
    }
}
