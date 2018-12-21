<?php
/**
 * @package    DPAttachments
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2012 - 2018 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

$path = JPATH_ROOT . '/templates/' . JFactory::getApplication()->getTemplate() . '/images/com_dpattachments/icons/' . $displayData['icon'] . '.svg';
if (!file_exists($path)) {
	$path = JPATH_ROOT . '/templates/' . JFactory::getApplication()->getTemplate() . '/images/icons/' . $displayData['icon'] . '.svg';
}
if (!file_exists($path)) {
	$path = JPATH_ROOT . '/media/com_dpattachments/images/icons/' . $displayData['icon'] . '.svg';
}
if (!file_exists($path)) {
	$path = JPATH_ROOT . '/media/com_dpattachments/images/icons/' . $displayData['icon'] . '-solid.svg';
}
if (!file_exists($path)) {
	return '';
}

$content = @file_get_contents($path);
if (!empty($displayData['title'])) {
	$content = str_replace('><path', '><title>' . $displayData['title'] . '</title><path', $content);
}
?>
<span class="dp-icon dp-icon_<?php echo $displayData['icon']; ?>"><?php echo $content; ?></span>
