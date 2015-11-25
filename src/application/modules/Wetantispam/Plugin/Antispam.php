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

    public function __construct()
    {
        $this->akismet = new Wetantispam_Service_Akismet();
    }

    /**
     * Check a contribution for spam.
     * Handles the 'onForumTopicCreateBefore', 'onForumPostCreateBefore', 'onMessagesMessageCreateBefore', 'onBlogCreateBefore', 'onCoreCommentCreateBefore' events.
     *
     * @param $event Engine_Hooks_Event
     */
    public function onForumTopicCreateBefore($event)
    {
        return $this->isSpam($event->getPayload()->description);
    }

    public function onForumPostCreateBefore($event)
    {
        return $this->isSpam($event->getPayload()->body);
    }

    public function onForumPostUpdateBefore($event)
    {
        return $this->isSpam($event->getPayload()->body);
    }

    public function onMessagesMessageCreateBefore($event)
    {
        return $this->isSpam($event->getPayload()->body);
    }

    public function onBlogCreateBefore($event)
    {
        return $this->isSpam($event->getPayload()->body);
    }

    public function onBlogUpdateBefore($event)
    {
        return $this->isSpam($event->getPayload()->body);
    }

    public function onCoreCommentCreateBefore($event)
    {
        return $this->isSpam($event->getPayload()->body);
    }

    /**
     * @param $content
     * @throws Exception
     * @throws Wetantispam_Plugin_Exception
     */
    private function isSpam($content)
    {
        $isSpam = $this->akismet->isSpam(array(
            'comment_content' => $content,
            // DEBUG: Force false positives
//             'comment_author' => 'viagra-test-123'
        ));

        if ($isSpam) {
            $this->createReport($content);
            throw new Wetantispam_Plugin_Exception($content);
        }
        return false;
    }

    /**
     * Create abuse report impersonating the current user reporting herself.
     * Include spam meesage in report.
     *
     * @param $content string Spam content
     * @throws Exception
     */

    private function createReport($content)
    {
        /** @var $table Core_Model_DbTable_Reports */
        $table = Engine_Api::_()->getItemTable('core_report');
        $db = $table->getAdapter();
        $db->beginTransaction();

        try {
            $viewer = Engine_Api::_()->user()->getViewer();

            $report = $table->createRow();
            $report->category = 'spam';
            $report->description = "Spam?\r\n\r\n--\r\n\r\n" . substr(strip_tags($content), 0, 1024) . "\r\n";
            $report->subject_type = $viewer->getType();
            $report->subject_id = $viewer->getIdentity();
            $report->user_id = $viewer->getIdentity();
            $report->save();

            // Increment report count
            Engine_Api::_()->getDbtable('statistics', 'core')->increment('core.reports');

            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

}
