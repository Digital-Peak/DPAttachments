<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2013 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

\defined('_JEXEC') or die();

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('script', 'com_dpattachments/views/form/edit.min.js', ['relative' => true, 'version' => 'auto'], ['defer' => true, 'type' => 'module']);

$hiddenSets = [];
// Custom fields are rendered as normal fields
foreach ($this->form->getFieldsets('com_fields') as $name => $fieldSet) {
	$hiddenSets[] = $name;
}

$this->set('hiddenFieldsets', $hiddenSets);

// Is needed as params assumes tabs
HTMLHelper::_('bootstrap.startTabSet');
?>
<div class="com-dpattachments-attachment-form">
	<form action="<?php echo Route::_('index.php?option=com_dpattachments&layout=edit&id=' . (int) $this->item->id); ?>"
		method="post" name="adminForm" id="item-form" class="dp-form form-validate form-horizontal">
		<?php echo LayoutHelper::render('joomla.edit.item_title', $this); ?>
		<div class="dp-form__content">
			<?php echo $this->form->renderField('title'); ?>
			<?php echo $this->form->renderField('item_id'); ?>
			<?php echo $this->form->renderField('context'); ?>
			<?php echo $this->form->renderField('path'); ?>
			<?php foreach ($this->form->getFieldsets('com_fields') as $name => $fieldSet) { ?>
				<?php foreach ($this->form->getFieldset($name) as $field) { ?>
					<?php echo $field->renderField(); ?>
				<?php } ?>
			<?php } ?>
			<?php echo LayoutHelper::render('joomla.edit.params', $this); ?>
			<?php echo $this->form->renderField('state'); ?>
			<?php echo $this->form->renderField('access'); ?>
			<?php echo $this->form->renderField('featured'); ?>
			<?php if ($this->item->hits) { ?>
				<?php echo $this->form->renderField('hits'); ?>
			<?php }?>
			<?php echo $this->form->renderField('id'); ?>
			<?php echo $this->form->renderField('created_by'); ?>
			<?php echo $this->form->renderField('created_by_alias'); ?>
			<?php echo $this->form->renderField('created'); ?>
			<?php echo $this->form->renderField('publish_up'); ?>
			<?php echo $this->form->renderField('publish_down'); ?>
			<?php if ($this->item->modified_by) { ?>
				<?php echo $this->form->renderField('modified_by'); ?>
				<?php echo $this->form->renderField('modified'); ?>
			<?php }?>
			<?php if ($this->item->version) { ?>
				<?php echo $this->form->renderField('version'); ?>
			<?php }?>

			<input type="hidden" name="task" value="" />
			<input type="hidden" name="return" value="<?php echo $this->input->getCmd('return');?>" />
			<?php echo HTMLHelper::_('form.token'); ?>
		</div>
	</form>
</div>
