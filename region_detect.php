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
        if (is_null($this->_db))
            $this->_db = JFactory::getDbo();
    }

    function onBeforeRender() {
		
    }
}

// End class
