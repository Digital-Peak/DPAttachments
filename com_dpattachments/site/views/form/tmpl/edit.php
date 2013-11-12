<?php
/**
 * @package    DPAttachments
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2012 - 2013 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

JHtml::_('behavior.keepalive');
JHtml::_('behavior.calendar');
JHtml::_('behavior.formvalidation');
JHtml::_('formbehavior.chosen', 'select');

$params = $this->state->get('params');
?>

<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'attachment.cancel' || document.formvalidator.isValid(document.id('adminForm')))
		{
			Joomla.submitform(task);
		}
	}
</script>
<div class="edit item-page<?php echo $this->pageclass_sfx; ?>">
	<?php if ($params->get('show_page_heading', 1))
	{ ?>
	<div class="page-header">
		<h1>
			<?php echo $this->escape($params->get('page_heading')); ?>
		</h1>
	</div>
	<?php
	}?>

	<form action="<?php echo JRoute::_('index.php?option=com_dpattachments&a_id=' . (int) $this->item->id); ?>"
		method="post" name="adminForm" id="adminForm" class="form-validate form-vertical">
		<div class="btn-toolbar">
			<div class="btn-group">
				<button type="button" class="btn btn-primary"
					onclick="Joomla.submitbutton('attachment.save')">
					<span class="icon-ok"></span>&#160;<?php echo JText::_('JSAVE')?>
				</button>
			</div>
			<div class="btn-group">
				<button type="button" class="btn"
					onclick="Joomla.submitbutton('attachment.cancel')">
					<span class="icon-cancel"></span>&#160;<?php echo JText::_('JCANCEL')?>
				</button>
			</div>
		</div>
		<fieldset>
			<ul class="nav nav-tabs">
				<li class="active"><a href="#editor" data-toggle="tab"><?php echo JText::_('COM_DPATTACHMENTS_VIEW_ATTACHMENT_DETAILS') ?></a></li>
				<li><a href="#publishing" data-toggle="tab"><?php echo JText::_('COM_DPATTACHMENTS_VIEW_ATTACHMENT_PUBLISHING') ?></a></li>
			</ul>

			<div class="tab-content">
				<div class="tab-pane active" id="editor">
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

					<?php if ($this->item->params->get('access-change'))
					{ ?>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('state'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('state'); ?>
						</div>
					</div>
					<?php
					} ?>

					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('tags'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('tags'); ?>
						</div>
					</div>
					<?php echo $this->form->getInput('description'); ?>
				</div>
				<div class="tab-pane" id="publishing">
					<?php if (is_null($this->item->id))
					{ ?>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('alias'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('alias'); ?>
						</div>
					</div>
					<?php
					}?>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('created_by_alias'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('created_by_alias'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('publish_up'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('publish_up'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('publish_down'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('publish_down'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('access'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('access'); ?>
						</div>
					</div>
				</div>
			</div>

			<input type="hidden" name="task" value="" />
			<input type="hidden" name="return" value="<?php echo $this->return_page; ?>" />
			<?php if ($this->params->get('enable_category', 0) == 1)
			{?>
				<input type="hidden" name="jform[catid]" value="<?php echo $this->params->get('catid', 1); ?>" />
			<?php
			}
			echo JHtml::_('form.token'); ?>
		</fieldset>
	</form>
</div>
