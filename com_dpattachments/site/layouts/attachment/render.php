<?php
/**
 * @package    DPAttachments
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2012 - 2018 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

$attachment = $displayData['attachment'];
if (!$attachment) {
	return;
}

JLoader::import('joomla.filesystem.folder');

JFactory::getLanguage()->load('com_dpattachments', JPATH_ADMINISTRATOR . '/components/com_dpattachments');

$previewExtensions = [];
foreach (JFolder::files(JPATH_SITE . '/components/com_dpattachments/views/attachment/tmpl') as $file) {
	$previewExtensions[] = JFile::stripExt($file);
}
?>
<div class="dp-attachment">
	<?php if (in_array(strtolower(JFile::getExt($attachment->path)), $previewExtensions)) { ?>
		<a href="<?php echo JRoute::_('index.php?option=com_dpattachments&view=attachment&tmpl=component&id=' . (int)$attachment->id); ?>"
		   class="dp-attachment__link">
			<?php echo $attachment->title; ?>
		</a>
	<?php } else { ?>
		<span class="dp-attachment__title"><?php echo $attachment->title; ?></span>
	<?php } ?>
	<span class="dp-attachment__size">[<?php echo \DPAttachments\Helper\Core::size($attachment->size); ?>]</span>
	<a href="<?php echo JRoute::_('index.php?option=com_dpattachments&task=attachment.download&id=' . (int)$attachment->id); ?>" target="_blank">
		<?php echo \DPAttachments\Helper\Core::renderLayout('block.icon', ['icon' => 'download']); ?>
	</a>
	<div class="dp-attachment__date">
		<?php $author = $attachment->created_by_alias ?: isset($attachment->author_name) ? $attachment->author_name : $attachment->created_by; ?>
		<?php echo JText::sprintf('COM_DPATTACHMENTS_TEXT_UPLOADED_LABEL', JHtmlDate::relative($attachment->created), $author); ?>
	</div>
	<div class="dp-attachment__actions">
		<?php if (\DPAttachments\Helper\Core::canDo('core.edit', $attachment->context, $attachment->item_id)) { ?>
			<a href="<?php echo JRoute::_('index.php?option=com_dpattachments&task=attachment.edit&a_id=' . $attachment->id . '&return=' .
				base64_encode(JUri::getInstance()->toString())); ?>" class="dp-button">
				<?php echo \DPAttachments\Helper\Core::renderLayout('block.icon', ['icon' => 'pencil-alt']); ?>
				<?php echo JText::_('JACTION_EDIT'); ?>
			</a>
		<?php } ?>
		<?php if (\DPAttachments\Helper\Core::canDo('core.edit.state', $attachment->context, $attachment->item_id)) { ?>
			<a href="<?php echo JRoute::_('index.php?option=com_dpattachments&task=attachment.publish&state=-2&id=' . $attachment->id . '&' . JSession::getFormToken() .
				'=1&return=' . base64_encode(JUri::getInstance()->toString())); ?>" class="dp-button">
				<?php echo \DPAttachments\Helper\Core::renderLayout('block.icon', ['icon' => 'trash-alt']); ?>
				<?php echo JText::_('JTRASH'); ?>
			</a>
		<?php } ?>
	</div>
</div>
