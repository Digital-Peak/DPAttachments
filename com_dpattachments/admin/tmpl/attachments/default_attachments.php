<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2021 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

\defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$app       = Factory::getApplication();
$user      = $this->getCurrentUser();
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
?>
<div class="com-dpattachments-attachments__attachments">
	<table class="table table-striped" id="dpattachmentList">
		<thead>
			<tr>
			<th width="1%">
				<?php echo HTMLHelper::_('grid.checkall'); ?>
			</th>
			<th width="1%" class="nowrap center">
				<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
			</th>
			<th>
				<?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
			</th>
			<th width="10%" class="nowrap">
				<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ACCESS', 'a.access', $listDirn, $listOrder); ?>
			</th>
			<th width="10%" class="nowrap">
				<?php echo HTMLHelper::_('searchtools.sort', 'JAUTHOR', 'a.created_by', $listDirn, $listOrder); ?>
			</th>
			<th width="10%" class="nowrap">
				<?php echo HTMLHelper::_('searchtools.sort', 'JDATE', 'a.created', $listDirn, $listOrder); ?>
			</th>
			<th width="10%">
				<?php echo HTMLHelper::_('searchtools.sort', 'COM_DPATTACHMENTS_FIELD_HITS_LABEL', 'a.hits', $listDirn, $listOrder); ?>
			</th>
			<th width="1%" class="nowrap">
				<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
			</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($this->items as $i => $item) {
				$item->max_ordering = 0;
				$ordering           = ($listOrder == 'a.ordering');
				$canEdit            = Factory::getApplication()->bootComponent('dpattachments')->canDo('core.edit', $item->context, $item->item_id);
				$canCheckin         = $user->authorise('core.manage', 'com_checkin') || $item->checked_out == $user->id || $item->checked_out == 0;
				$canEditOwn         = Factory::getApplication()->bootComponent('dpattachments')->canDo('core.edit.own', $item->context, $item->item_id);
				$canChange          = Factory::getApplication()->bootComponent('dpattachments')->canDo('core.edit.state', $item->context, $item->item_id) && $canCheckin;
			?>
			<tr class="dp-attachment row<?php echo $i % 2; ?>" sortable-group-id="<?php echo $item->context; ?>">
				<td class="center"><?php echo HTMLHelper::_('grid.id', $i, $item->id); ?></td>
				<td class="center">
					<div class="btn-group">
						<?php echo HTMLHelper::_('jgrid.published', $item->state, $i, 'attachments.', $canChange, 'cb', $item->publish_up, $item->publish_down); ?>
					</div>
				</td>
				<td class="nowrap has-context">
					<div class="pull-left">
						<?php if ($item->checked_out) { ?>
							<?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'dpattachments.', $canCheckin); ?>
						<?php } ?>
						<?php if ($canEdit || $canEditOwn) { ?>
							<a href="<?php echo Route::_('index.php?option=com_dpattachments&task=attachment.edit&id=' . $item->id); ?>"
									title="<?php echo Text::_('JACTION_EDIT'); ?>">
								<?php echo $this->escape($item->title); ?>
							</a>
						<?php } else { ?>
							<span title="<?php echo Text::sprintf('JFIELD_ALIAS_LABEL', $this->escape($item->alias)); ?>">
								<?php echo $this->escape($item->title); ?>
							</span>
						<?php } ?>
						<p>
							<?php echo Text::_('COM_DPATTACHMENTS_FIELD_CONTEXT_LABEL') . ': ' . $this->escape($this->renderContext($item->context)); ?>
							<br/>
							<?php echo Text::_('COM_DPATTACHMENTS_FIELD_ITEM_ID_LABEL') . ': ' . $this->escape($item->item_id); ?>
						</p>
					</div>
				</td>
				<td><?php echo $this->escape($item->access_level); ?></td>
				<td>
					<a href="<?php echo Route::_('index.php?option=com_users&task=user.edit&id=' . (int) $item->created_by); ?>"
							title="<?php echo Text::_('JAUTHOR'); ?>">
						<?php echo $this->escape($item->author_name); ?>
					</a>
				</td>
				<td class="nowrap"><?php echo HTMLHelper::_('date', $item->created, Text::_('DATE_FORMAT_LC4')); ?></td>
				<td class="center"><?php echo (int) $item->hits; ?></td>
				<td class="center"><?php echo (int) $item->id; ?></td>
			</tr>
			<?php } ?>
		</tbody>
	</table>
	<?php echo $this->pagination->getListFooter(); ?>
</div>
