<?php
/**
 * @package    DPAttachments
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2012 - 2019 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

$itemId = $displayData['itemId'];
if (!$itemId) {
	return;
}
$context = $displayData['context'];
if (!$context) {
	return;
}
JFactory::getLanguage()->load('com_dpattachments', JPATH_ADMINISTRATOR . '/components/com_dpattachments');

JHtml::_('stylesheet', 'com_dpattachments/layouts/attachment/form.min.css', ['relative' => true]);

JHtml::_('behavior.core');
JHtml::_('script', 'com_dpattachments/layouts/attachment/form.min.js', ['relative' => true], ['defer' => true]);
?>
<div class="com-dpattachments-layout-form">
	<form action="<?php echo JRoute::_('index.php?option=com_dpattachments&task=attachment.upload'); ?>" method="post"
		  class="com-dpattachments-layout-form__form dp-form">
		<div class="dp-form__upload dp-upload">
			<span class="dp-upload__select"><?php echo JText::_('COM_DPATTACHMENTS_TEXT_SELECT_FILE'); ?></span>
			<span class="dp-upload__paste"><?php echo JText::_('COM_DPATTACHMENTS_TEXT_PASTE'); ?></span>
		</div>
		<div class="dp-form__input dp-input">
			<input type="file" name="file" class="dp-input__file" id="dp-input-<?php echo $itemId; ?>">
			<label for="dp-input-<?php echo $itemId; ?>" class="dp-input__label">
				<?php echo \DPAttachments\Helper\Core::renderLayout('block.icon', ['icon' => 'upload']); ?>
				<?php echo JText::_('COM_DPATTACHMENTS_BUTTON_SELECT_FILE'); ?>
			</label>
		</div>
		<input type="hidden" name="attachment[context]" value="<?php echo $context; ?>"/>
		<input type="hidden" name="attachment[item_id]" value="<?php echo $itemId; ?>"/>
		<?php JHtml::_('form.token'); ?>
		<progress max="100" value="0" class="dp-form__progress"></progress>
	</form>
</div>
