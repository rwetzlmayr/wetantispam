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

class Wetantispam_Form_Admin_Settings extends Engine_Form
{
    public function init()
    {
        $this->setTitle('Wet Antispam');
        $this->setAttrib('class', 'global_form_popup');

        // Akismet API Key
        $this->addElement('Text', 'akismetapikey', array(
            'label' => 'Akismet API  Key:',
            'required' => true,
            'allowEmpty' => false,
            'value' => Engine_Api::_()->getApi('settings', 'core')->wetantispam_akismet_apikey,
            'validators' => array(
                array('NotEmpty', true)
            ),
            'filters' => array(
                'StringTrim',
            ),
        ));
        $this->getElement('akismetapikey')->getValidator('NotEmpty')
            ->setMessage('Please fill in the Akismet API key.', 'isEmpty');

        // Minimum trust level
        $this->addElement('Text', 'trustlevel', array(
            'label' => 'Member\'s minimum trust level:',
            'required' => true,
            'allowEmpty' => false,
            'value' => Engine_Api::_()->getApi('settings', 'core')->wetantispam_trustlevel,
            'validators' => array(
                array('NotEmpty', true),
                array('Int'),
                array('Between', true, array('min' => 0, 'max' => 999)),
            ),
            'filters' => array(
                'StringTrim',
            ),
        ));

        $this->addElement('Button', 'submit', array(
            'label' => 'Save',
            'type' => 'submit',
            'ignore' => true,
        ));
    }
}
