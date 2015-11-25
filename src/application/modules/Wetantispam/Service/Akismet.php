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

class Wetantispam_Service_Akismet extends Zend_Service_Akismet
{
    public function __construct()
    {
        $baseurl = "http://{$_SERVER['HTTP_HOST']}/";
        $apikey = $this->getApiKey();
        if (empty($apikey)) {
            throw new Wetantispam_Plugin_Exception(
                'Akismet API key not set. Please fix this issue by setting the Akismet API key in this plugin\'s admin panel.'
            );
        }
        parent::__construct($apikey, $baseurl);
    }

    public function getApiKey()
    {
        return Engine_Api::_()->getApi('settings', 'core')->wetantispam_akismet_apikey;
    }

    public function isSpam($params)
    {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        } else {
            $ip = '';
        }

        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $userAgent = (string)$_SERVER['HTTP_USER_AGENT'];
        } else {
            $userAgent = '';
        }

        if (isset($_SERVER['HTTP_REFERER'])) {
            $referrer = (string)$_SERVER['HTTP_REFERER'];
        } else {
            $referrer = '';
        }

        $params['user_ip'] = $ip;
        $params['user_agent'] = $userAgent;
        if ($referrer != '') {
            $params ['referrer'] = $referrer;
        }

        return parent::isSpam($params);
    }
}
