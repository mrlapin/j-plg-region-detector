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
        if (isset($_COOKIE['user_region']))
            $region = $_COOKIE['user_region'];
        elseif (($result = $this->_get_region()) !== false) {
            setcookie('user_region', $result, time() + 604800, "/");
            $region = $result;
        }

        $app->set("JPLG_REGION_DETECTOR", $region);

        unset($region);
        unset($result);
    }

    function _get_region($to = 'utf-8') {
        // Создаем новый временный файл для записи ответа от geoIp
        $xml_file_name = uniqid() . ".xml";
        $result = false;
        
        if (($fp = fopen($xml_file_name, "w")) !== false) {
            // Открываем сокет
            $socket = fsockopen('ipgeobase.ru', 7020, $errno, $errstr, 2);
            if ($socket) {
                // Отсылаем заголовок с IP
                $out = "GET /geo?ip=" . $_SERVER['REMOTE_ADDR'] . " HTTP/1.1\r\n";
                $out .= "Host: ipgeobase.ru\r\n";
                $out .= "Connection: Close\r\n\r\n";
                fwrite($socket, $out);

                // Читаем ответ и записываем во временный файл
                $xml_started = false;
                while (!feof($socket)) {
                    $str = fgets($socket);
                    if (substr_count($str, 'xml version=') > 0)
                        $xml_started = true;
                    if ($xml_started)
                        fwrite ($fp, $str);
                }
                fclose($fp);
                fclose($socket);

                // Читаем файл с ответом
                $xml = simplexml_load_file($xml_file_name);
                if($xml) {
                    if(!$xml->ip->message) {
                        if($to == 'utf-8')
                            $result = $xml->ip->region;
                        else {
                            if(function_exists('iconv'))
                                $result = iconv('UTF-8', $to . "//IGNORE", $xml->ip->region);
                        }
                    }
                }
            }
            // Удаляем временный файл с ответом
            unlink($xml_file_name);
        }
        return $result;
    }

    /*
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
     */
}

// End class
