<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2018 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

defined('_JEXEC') or die();

use Joomla\CMS\Factory;

$path = JPATH_ROOT . '/templates/' . Factory::getApplication()->getTemplate() . '/images/com_dpattachments/icons/' . $displayData['icon'] . '.svg';
if (!file_exists($path)) {
	$path = JPATH_ROOT . '/templates/' . Factory::getApplication()->getTemplate() . '/images/icons/' . $displayData['icon'] . '.svg';
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
