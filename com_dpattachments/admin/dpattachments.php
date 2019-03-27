<?php
/**
 * @package    DPAttachments
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2012 - 2019 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

if (!JFactory::getUser()->authorise('core.manage', 'com_dpattachments')) {
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

JLoader::import('components.com_dpattachments.libraries.vendor.autoload', JPATH_ADMINISTRATOR);

$controller = JControllerLegacy::getInstance('DPAttachments');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
