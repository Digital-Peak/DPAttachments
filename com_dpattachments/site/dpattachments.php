<?php
/**
 * @package    DPAttachments
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2012 - 2019 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

JFactory::getLanguage()->load('com_dpattachments', JPATH_ADMINISTRATOR . '/components/com_dpattachments');
JLoader::import('components.com_dpattachments.vendor.autoload', JPATH_ADMINISTRATOR);

$controller = JControllerLegacy::getInstance('DPAttachments');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
