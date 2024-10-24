<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2016 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

\defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$itemId = $displayData['itemId'];
if (!$itemId) {
	return;
}
$context = $displayData['context'];
if (!$context) {
	return;
}
$displayData['app']->getLanguage()->load('com_dpattachments', JPATH_ADMINISTRATOR . '/components/com_dpattachments');

HTMLHelper::_('stylesheet', 'com_dpattachments/dpattachments/layouts/attachment/form.min.css', ['relative' => true]);

HTMLHelper::_('behavior.core');
HTMLHelper::_('script', 'com_dpattachments/layouts/attachment/form.min.js', ['relative' => true], ['defer' => true, 'type' => 'module']);
?>
<div class="com-dpattachments-layout-form"
	data-upload-url="<?php echo Route::_('index.php?option=com_dpattachments&task=attachment.upload'); ?>"
	data-context=<?php echo $context; ?>
	data-item=<?php echo $itemId; ?>>
	<div class="dp-form__upload dp-upload">
		<span class="dp-upload__select"><?php echo Text::_('COM_DPATTACHMENTS_TEXT_SELECT_FILE'); ?></span>
		<span class="dp-upload__paste"><?php echo Text::_('COM_DPATTACHMENTS_TEXT_PASTE'); ?></span>
	</div>
	<div class="dp-form__input dp-input">
		<input type="file" name="file" class="dp-input__file" id="dp-input-<?php echo $context . '-' . $itemId; ?>">
		<label for="dp-input-<?php echo $context . '-' . $itemId; ?>" class="dp-input__label">
			<?php echo Factory::getApplication()->bootComponent('dpattachments')->renderLayout('block.icon', ['icon' => 'upload']); ?>
			<?php echo Text::_('COM_DPATTACHMENTS_BUTTON_SELECT_FILE'); ?>
		</label>
	</div>
	<progress max="100" value="0" class="dp-form__progress"></progress>
</div>
