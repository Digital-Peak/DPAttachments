<?php
/**
 * @package    DPAttachments
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2012 - 2013 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

JLoader::register('DPAttachmentsHelper', JPATH_COMPONENT_ADMINISTRATOR . '/helpers/dpattachments.php');
JFactory::getLanguage()->load('com_dpattachments', JPATH_ADMINISTRATOR . '/components/com_dpattachments');

$controller = JControllerLegacy::getInstance('DPAttachments');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
