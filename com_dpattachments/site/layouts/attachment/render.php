<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2016 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

defined('_JEXEC') or die();

use DigitalPeak\Component\DPAttachments\Administrator\Extension\DPAttachmentsComponent;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;

$attachment = $displayData['attachment'];
if (!$attachment) {
	return;
}

/** @var CMSApplicationInterface $app */
$app = $displayData['app'] ?? Factory::getApplication();
$app->getLanguage()->load('com_dpattachments', JPATH_ADMINISTRATOR . '/components/com_dpattachments');

$component = $app->bootComponent('dpattachments');
if (!$component instanceof DPAttachmentsComponent) {
	return;
}

$previewExtensions = [];
foreach (Folder::files(JPATH_SITE . '/components/com_dpattachments/tmpl/attachment') as $file) {
	$previewExtensions[] = File::stripExt($file);
}
?>
<div class="dp-attachment">
	<?php if (in_array(strtolower(pathinfo($attachment->path, PATHINFO_EXTENSION)), $previewExtensions)) { ?>
		<a href="<?php echo Route::link('site', 'index.php?option=com_dpattachments&view=attachment&tmpl=component&id=' . (int)$attachment->id); ?>"
		   class="dp-attachment__link">
			<?php echo $attachment->title; ?>
		</a>
	<?php } else { ?>
		<span class="dp-attachment__title"><?php echo $attachment->title; ?></span>
	<?php } ?>
	<span class="dp-attachment__size">[<?php echo $component->size($attachment->size); ?>]</span>
	<a href="<?php echo Route::_('index.php?option=com_dpattachments&task=attachment.download&id=' . (int)$attachment->id); ?>" target="_blank">
		<?php echo $component->renderLayout('block.icon', ['icon' => 'download']); ?>
	</a>
	<?php if (!empty($attachment->event) && !empty($attachment->event->afterDisplayTitle)) { ?>
		<div class="dp-attachment__after-title"><?php echo $attachment->event->afterDisplayTitle; ?></div>
	<?php } ?>
	<?php if (!empty($attachment->event) && !empty($attachment->event->beforeDisplayAttachment)) { ?>
		<div class="dp-attachment__before-display"><?php echo $attachment->event->beforeDisplayAttachment; ?></div>
	<?php } ?>
	<div class="dp-attachment__date">
		<?php $author = $attachment->created_by_alias ?: (isset($attachment->author_name) ? $attachment->author_name : $attachment->created_by); ?>
		<?php echo sprintf($app->getLanguage()->_('COM_DPATTACHMENTS_TEXT_UPLOADED_LABEL'), HTMLHelper::_('date.relative', $attachment->created), $author); ?>
	</div>
	<?php if (!empty($attachment->event) && !empty($attachment->event->afterDisplayAttachment)) { ?>
		<div class="dp-attachment__after-display"><?php echo $attachment->event->afterDisplayAttachment; ?></div>
	<?php } ?>
	<div class="dp-attachment__actions">
		<?php if ($component->canDo('core.edit', $attachment->context, $attachment->item_id)) { ?>
			<a href="<?php echo Route::_('index.php?option=com_dpattachments&task=attachment.edit&id=' . $attachment->id . ($app->isClient('site') ? '&tmpl=component' :'')); ?>"
				class="dp-button dp-button-edit">
				<?php echo $component->renderLayout('block.icon', ['icon' => 'pencil-alt']); ?>
				<?php echo $app->getLanguage()->_('JACTION_EDIT'); ?>
			</a>
		<?php } ?>
		<?php if ($component->canDo('core.edit.state', $attachment->context, $attachment->item_id)) { ?>
			<a href="<?php echo Route::_('index.php?option=com_dpattachments&task=attachment.publish&state=-2&id='
				. $attachment->id . '&' . Session::getFormToken() . '=1'); ?>" class="dp-button dp-button-trash">
				<?php echo $component->renderLayout('block.icon', ['icon' => 'trash-alt']); ?>
				<?php echo $app->getLanguage()->_('JTRASH'); ?>
			</a>
		<?php } ?>
	</div>
</div>
