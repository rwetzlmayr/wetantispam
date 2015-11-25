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

class Wetantispam_AdminIndexController extends Core_Controller_Action_Admin
{
    public function indexAction()
    {
        $form = $this->view->form = new Wetantispam_Form_Admin_Settings();

        if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
            $db = Engine_Api::_()->getDbtable('settings', 'core')->getAdapter();
            $db->beginTransaction();
            try {
                Engine_Api::_()->getApi('settings', 'core')->wetantispam_akismet_apikey = $form->getValue('akismetapikey');
                $db->commit();
                $form->addNotice('Your changes have been saved.');
            } catch (Exception $e) {
                $db->rollback();
                $form->addError('There was a problem saving the new settings; please try again.');
                return;
            }
        }
    }
}
