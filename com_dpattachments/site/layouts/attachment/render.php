<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2016 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;

$attachment = $displayData['attachment'];
if (!$attachment) {
	return;
}

Factory::getLanguage()->load('com_dpattachments', JPATH_ADMINISTRATOR . '/components/com_dpattachments');

$previewExtensions = [];
foreach (Folder::files(JPATH_SITE . '/components/com_dpattachments/tmpl/attachment') as $file) {
	$previewExtensions[] = File::stripExt($file);
}
?>
<div class="dp-attachment">
	<?php if (in_array(strtolower(File::getExt($attachment->path)), $previewExtensions)) { ?>
		<a href="<?php echo Route::_('index.php?option=com_dpattachments&view=attachment&tmpl=component&id=' . (int)$attachment->id); ?>"
		   class="dp-attachment__link">
			<?php echo $attachment->title; ?>
		</a>
	<?php } else { ?>
		<span class="dp-attachment__title"><?php echo $attachment->title; ?></span>
	<?php } ?>
	<span class="dp-attachment__size">[<?php echo Factory::getApplication()->bootComponent('dpattachments')->size($attachment->size); ?>]</span>
	<a href="<?php echo Route::_('index.php?option=com_dpattachments&task=attachment.download&id=' . (int)$attachment->id); ?>" target="_blank">
		<?php echo Factory::getApplication()->bootComponent('dpattachments')->renderLayout('block.icon', ['icon' => 'download']); ?>
	</a>
	<div class="dp-attachment__date">
		<?php $author = $attachment->created_by_alias ?: (isset($attachment->author_name) ? $attachment->author_name : $attachment->created_by); ?>
		<?php echo Text::sprintf('COM_DPATTACHMENTS_TEXT_UPLOADED_LABEL', HTMLHelper::_('date.relative', $attachment->created), $author); ?>
	</div>
	<div class="dp-attachment__actions">
		<?php if (Factory::getApplication()->bootComponent('dpattachments')->canDo('core.edit', $attachment->context, $attachment->item_id)) { ?>
			<a href="<?php echo Route::_('index.php?option=com_dpattachments&task=attachment.edit&a_id=' . $attachment->id . '&return=' .
				base64_encode(Uri::getInstance()->toString())); ?>" class="dp-button">
				<?php echo Factory::getApplication()->bootComponent('dpattachments')->renderLayout('block.icon', ['icon' => 'pencil-alt']); ?>
				<?php echo Text::_('JACTION_EDIT'); ?>
			</a>
		<?php } ?>
		<?php if (Factory::getApplication()->bootComponent('dpattachments')->canDo('core.edit.state', $attachment->context, $attachment->item_id)) { ?>
			<a href="<?php echo Route::_('index.php?option=com_dpattachments&task=attachment.publish&state=-2&id=' . $attachment->id . '&' . Session::getFormToken() .
				'=1&return=' . base64_encode(Uri::getInstance()->toString())); ?>" class="dp-button">
				<?php echo Factory::getApplication()->bootComponent('dpattachments')->renderLayout('block.icon', ['icon' => 'trash-alt']); ?>
				<?php echo Text::_('JTRASH'); ?>
			</a>
		<?php } ?>
	</div>
</div>
