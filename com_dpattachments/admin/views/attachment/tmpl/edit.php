<?php
/**
 * @package    DPAttachments
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2012 - 2014 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', 'select');

// Create shortcut to parameters.
$params = $this->state->get('params');
$params = $params->toArray();

$app = JFactory::getApplication();
$input = $app->input;
?>

<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'attachment.cancel' || document.formvalidator.isValid(document.id('item-form')))
		{
			Joomla.submitform(task, document.getElementById('item-form'));
		}
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_dpattachments&layout=edit&id=' . (int) $this->item->id); ?>"
	method="post" name="adminForm" id="item-form" class="form-validate">

	<?php echo JLayoutHelper::render('joomla.edit.item_title', $this); ?>

	<div class="row-fluid">
		<div class="span10 form-horizontal">
			<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'general')); ?>
			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'general', JText::_('COM_DPATTACHMENTS_VIEW_ATTACHMENT_DETAILS', true)); ?>
			<fieldset class="adminform">
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('title'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('title'); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('item_id'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('item_id'); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('context'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('context'); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('path'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('path'); ?>
					</div>
				</div>
			</fieldset>

			<?php echo JHtml::_('bootstrap.endTab'); ?>

			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'publishing', JText::_('COM_DPATTACHMENTS_VIEW_ATTACHMENT_PUBLISHING', true)); ?>
			<div class="row-fluid">
				<div class="span6">
					<div class="control-group">
						<?php echo $this->form->getLabel('alias'); ?>
						<div class="controls">
							<?php echo $this->form->getInput('alias'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('id'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('id'); ?>
						</div>
					</div>
					<div class="control-group">
						<?php echo $this->form->getLabel('created_by'); ?>
						<div class="controls">
							<?php echo $this->form->getInput('created_by'); ?>
						</div>
					</div>
					<div class="control-group">
						<?php echo $this->form->getLabel('created_by_alias'); ?>
						<div class="controls">
							<?php echo $this->form->getInput('created_by_alias'); ?>
						</div>
					</div>
					<div class="control-group">
						<?php echo $this->form->getLabel('created'); ?>
						<div class="controls">
							<?php echo $this->form->getInput('created'); ?>
						</div>
					</div>
				</div>
				<div class="span6">
					<div class="control-group">
						<?php echo $this->form->getLabel('publish_up'); ?>
						<div class="controls">
							<?php echo $this->form->getInput('publish_up'); ?>
						</div>
					</div>
					<div class="control-group">
						<?php echo $this->form->getLabel('publish_down'); ?>
						<div class="controls">
							<?php echo $this->form->getInput('publish_down'); ?>
						</div>
					</div>
					<?php if ($this->item->modified_by)
					{?>
						<div class="control-group">
							<?php echo $this->form->getLabel('modified_by'); ?>
							<div class="controls">
								<?php echo $this->form->getInput('modified_by'); ?>
							</div>
					   </div>
					   <div class="control-group">
							<?php echo $this->form->getLabel('modified'); ?>
							<div class="controls">
								<?php echo $this->form->getInput('modified'); ?>
							</div>
					   </div>
					<?php
					}?>

					<?php if ($this->item->version)
					{ ?>
						<div class="control-group">
							<?php echo $this->form->getLabel('version'); ?>
							<div class="controls">
								<?php echo $this->form->getInput('version'); ?>
							</div>
					    </div>
					<?php
					}?>

					<?php if ($this->item->hits)
					{ ?>
						<div class="control-group">
						    <div class="control-label">
							 <?php echo $this->form->getLabel('hits'); ?>
    						</div>
    						<div class="controls">
    							<?php echo $this->form->getInput('hits'); ?>
    						</div>
					   </div>
				    <?php
					}?>
				</div>
			</div>
			<?php echo JHtml::_('bootstrap.endTab'); ?>
			<?php echo JHtml::_('bootstrap.endTabSet'); ?>

			<input type="hidden" name="task" value="" />
			<input type="hidden" name="return" value="<?php echo $input->getCmd('return');?>" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
		<?php echo JLayoutHelper::render('joomla.edit.details', $this); ?>
	</div>
</form>
