<?php
/**
 * @package    DPAttachments
 * @copyright  (C) 2018 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

?>
<div class="com-dpattachments-form-edit__actions">
	<div class="dp-button-group">
		<button type="button" class="dp-button dp-button-save" data-task="save">
			<?php echo JLayoutHelper::render('block.icon', ['icon' => 'check']); ?>
			<?php echo JText::_('JSAVE'); ?>
		</button>
		<button type="button" class="dp-button dp-button-cancel" data-task="cancel">
			<?php echo JLayoutHelper::render('block.icon', ['icon' => 'ban']); ?>
			<?php echo JText::_('JCANCEL'); ?>
		</button>
	</div>
</div>
