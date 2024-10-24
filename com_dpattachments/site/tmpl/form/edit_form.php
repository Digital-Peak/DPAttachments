<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2018 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

\defined('_JEXEC') or die();

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

$hiddenSets = [];
// Custom fields are rendered as normal fields
foreach ($this->form->getFieldsets('com_fields') as $name => $fieldSet) {
	$hiddenSets[] = $name;
}

$this->set('hiddenFieldsets', $hiddenSets);

// Is needed as params assumes tabs
HTMLHelper::_('bootstrap.startTabSet');
?>
<div class="com-dpattachments-attachment-form__form">
	<form action="<?php echo Route::_('index.php?option=com_dpattachments&id=' . (int)$this->item->id); ?>"
		method="post" name="adminForm" class="dp-form form-validate">
		<fieldset class="dp-form__basic">
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
			<?php if ($this->item->params->get('access-change')) { ?>
				<?php echo $this->form->renderField('state'); ?>
			<?php } ?>
			<?php echo $this->form->renderField('created_by_alias'); ?>
			<?php echo $this->form->renderField('publish_up'); ?>
			<?php echo $this->form->renderField('publish_down'); ?>
			<?php echo $this->form->renderField('access'); ?>
		</fieldset>
		<input type="hidden" name="task" value=""/>
		<input type="hidden" name="return" value="<?php echo $this->return_page; ?>"/>
		<?php echo $this->form->getInput('case_id'); ?>
		<?php echo HTMLHelper::_('form.token'); ?>
	</form>
</div>
