<?php

/**
 * @version		0.1
 * @package		Region_detector
 * @author    	Jookolas
 * @copyright	Copyright (c) 2013. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.plugin.plugin');
if (version_compare(JVERSION, '1.6.0', 'ge')) {
    jimport('joomla.html.parameter');
}

class plgSystemRegion_detector extends JPlugin {

    function plgSystemRegion_detector(&$subject, $params) {
        parent::__construct($subject, $params);
    }

    function onAfterInitialise() {
        $app = &JFactory::getApplication();
        
        $region = "Москва";
        if(($result = $this->_get_region()) !== false)    
            $region = $result;
        
        $app->set("JPLG_REGION_DETECTOR", $region);
        
        unset($region);
        unset($result);
    }

    private function _get_region($ip = '', $to = 'utf-8') {
        $ip = ($ip) ? $ip : $_SERVER['REMOTE_ADDR'];
        // Запрос региона пользователя
        $xml = simplexml_load_file('http://ipgeobase.ru:7020/geo?ip=' . $ip);
        
        if ($xml->ip->message) {
            // Если в ответе пришло сообщение, то считаем запрос неудачным
            
            return $xml->ip->message;
        } else {
            if ($to == 'utf-8') {
                // Ответ по умолчанию приходит в utf-8
                return $xml->ip->region;
            } else {
                // Если требуется другая кодировка - конвертируем
                if (function_exists('iconv'))
                    return iconv("UTF-8", $to . "//IGNORE", $xml->ip->region);
                else
                    // Если iconv не поддерживается сервером - нудача
                    return false;
            }
        }
    }

}

// End class
