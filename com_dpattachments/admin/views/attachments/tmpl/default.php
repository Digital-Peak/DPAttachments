<?php
/**
 * @package    DPAttachments
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2012 - 2020 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('dropdown.init');
JHtml::_('formbehavior.chosen', 'select');

$app = JFactory::getApplication();
$user = JFactory::getUser();
$userId = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
$archived = $this->state->get('filter.published') == 2 ? true : false;
$trashed = $this->state->get('filter.published') == - 2 ? true : false;

$sortFields = $this->getSortFields();
?>

<form action="<?php echo JRoute::_('index.php?option=com_dpattachments&view=attachments'); ?>" method="post" name="adminForm" id="adminForm">
<?php if (!empty( $this->sidebar))
{ ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php
}
else
{ ?>
	<div id="j-main-container">
<?php
}?>
		<div id="filter-bar" class="btn-toolbar">
				<div class="filter-search btn-group pull-left">
					<label for="filter_search" class="element-invisible"><?php echo JText::_('COM_DPATTACHMENTS_FILTER_SEARCH_DESC'); ?></label>
					<input type="text" name="filter_search" id="filter_search"
						placeholder="<?php echo JText::_('JSEARCH_FILTER'); ?>"
						value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
						class="hasTooltip"
						title="<?php echo JHtml::tooltipText('COM_DPATTACHMENTS_FILTER_SEARCH_DESC'); ?>" />
				</div>
				<div class="btn-group pull-left hidden-phone">
					<button type="submit" class="btn hasTooltip"
						title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_SUBMIT'); ?>">
						<i class="icon-search"></i>
					</button>
					<button type="button" class="btn hasTooltip"
						title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_CLEAR'); ?>"
						onclick="document.id('filter_search').value='';this.form.submit();">
						<i class="icon-remove"></i>
					</button>
				</div>
				<div class="btn-group pull-right hidden-phone">
					<label for="limit" class="element-invisible"><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC'); ?></label>
				<?php echo $this->pagination->getLimitBox(); ?>
			</div>
				<div class="btn-group pull-right hidden-phone">
					<label for="directionTable" class="element-invisible"><?php echo JText::_('JFIELD_ORDERING_DESC'); ?></label>
					<select name="directionTable" id="directionTable"
						class="input-medium" onchange="Joomla.orderTable()">
						<option value=""><?php echo JText::_('JFIELD_ORDERING_DESC'); ?></option>
						<option value="asc"
							<?php echo $listDirn == 'asc' ? 'selected="selected"' : ''; ?>>
							<?php echo JText::_('JGLOBAL_ORDER_ASCENDING'); ?>
						</option>
						<option value="desc"
							<?php echo $listDirn == 'desc' ? 'selected="selected"' : ''; ?>>
							<?php echo JText::_('JGLOBAL_ORDER_DESCENDING');  ?>
						</option>
					</select>
				</div>
				<div class="btn-group pull-right">
					<label for="sortTable" class="element-invisible"><?php echo JText::_('JGLOBAL_SORT_BY'); ?></label>
					<select name="sortTable" id="sortTable" class="input-medium"
						onchange="Joomla.orderTable()">
						<option value=""><?php echo JText::_('JGLOBAL_SORT_BY');?></option>
					<?php echo JHtml::_('select.options', $sortFields, 'value', 'text', $listOrder); ?>
				</select>
				</div>
			</div>
			<div class="clearfix"></div>

			<table class="table table-striped" id="dpattachmentList">
				<thead>
					<tr>
					<th width="1%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', '<i class="icon-menu-2"></i>', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING'); ?>
					</th>
					<th width="1%" class="hidden-phone">
						<?php echo JHtml::_('grid.checkall'); ?>
					</th>
					<th width="1%" style="min-width: 55px" class="nowrap center">
						<?php echo JHtml::_('grid.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
					</th>
					<th>
						<?php echo JHtml::_('grid.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
					</th>
					<th width="10%" class="nowrap hidden-phone">
						<?php echo JHtml::_('grid.sort',  'JGRID_HEADING_ACCESS', 'a.access', $listDirn, $listOrder); ?>
					</th>
					<th width="10%" class="nowrap hidden-phone">
						<?php echo JHtml::_('grid.sort',  'JAUTHOR', 'a.created_by', $listDirn, $listOrder); ?>
					</th>
					<th width="10%" class="nowrap hidden-phone">
						<?php echo JHtml::_('grid.sort', 'JDATE', 'a.created', $listDirn, $listOrder); ?>
					</th>
					<th width="10%">
						<?php echo JHtml::_('grid.sort', 'JGLOBAL_HITS', 'a.hits', $listDirn, $listOrder); ?>
					</th>
					<th width="1%" class="nowrap hidden-phone">
						<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
					</th>
					</tr>
				</thead>
				<tbody>
			<?php foreach ( $this->items as $i => $item )
			{
                        $item->max_ordering = 0;
                        $ordering = ($listOrder == 'a.ordering');
                        $canEdit = \DPAttachments\Helper\Core::canDo('core.edit', $item->context, $item->item_id);
                        $canCheckin = $user->authorise('core.manage', 'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
                        $canEditOwn = \DPAttachments\Helper\Core::canDo('core.edit.own', $item->context, $item->item_id);
                        $canChange = \DPAttachments\Helper\Core::canDo('core.edit.state', $item->context, $item->item_id) && $canCheckin;
                        ?>
				<tr class="row<?php echo $i % 2; ?>"
					sortable-group-id="<?php echo $item->context; ?>">
				    <td class="order nowrap center hidden-phone">
						<?php
                        $iconClass = '';
                        if (! $canChange)
                        {
                            $iconClass = ' inactive';
                        }
                        ?>
					</td>
						<td class="center hidden-phone">
						<?php echo JHtml::_('grid.id', $i, $item->id); ?>
					</td>
						<td class="center">
							<div class="btn-group">
							<?php echo JHtml::_('jgrid.published', $item->state, $i, 'attachments.', $canChange, 'cb', $item->publish_up, $item->publish_down); ?>
						</div>
						</td>
						<td class="nowrap has-context">
							<div class="pull-left">
							<?php if ($item->checked_out)
							{
								echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'dpattachments.', $canCheckin);
							}
							if ($canEdit || $canEditOwn)
							{ ?>
								<a
									href="<?php echo JRoute::_('index.php?option=com_dpattachments&task=attachment.edit&id=' . $item->id); ?>"
									title="<?php echo JText::_('JACTION_EDIT'); ?>">
									<?php echo $this->escape($item->title); ?></a>
							<?php
							}
							else
							{ ?>
								<span
									title="<?php echo JText::sprintf('JFIELD_ALIAS_LABEL', $this->escape($item->alias)); ?>">
									<?php echo $this->escape($item->title); ?>
								</span>
							<?php
							} ?>
							     <p class="small">
                                    <?php
        							echo JText::_('COM_DPATTACHMENTS_FIELD_CONTEXT_LABEL') . ': ' . $this->escape(\DPAttachments\Helper\DPAttachmentsHelper::renderContext($item->context));
        							?>
        							<br/>
                                    <?php
        							echo JText::_('COM_DPATTACHMENTS_FIELD_ITEM_ID_LABEL') . ': ' . $this->escape($item->item_id);
        							?>
    							 </p>
							</div>
							<div class="pull-left">
							<?php
						    // Create dropdown items
						    JHtml::_('dropdown.edit', $item->id, 'attachment');
						    JHtml::_('dropdown.divider');
						    if ($item->state)
						    {
						        JHtml::_('dropdown.unpublish', 'cb' . $i, 'attachments.');
						    }
						    else
						    {
						        JHtml::_('dropdown.publish', 'cb' . $i, 'attachments.');
						    }

						    JHtml::_('dropdown.divider');

						    if ($archived)
						    {
						        JHtml::_('dropdown.unarchive', 'cb' . $i, 'attachments.');
						    }
						    else
						    {
						        JHtml::_('dropdown.archive', 'cb' . $i, 'attachments.');
						    }

						    if ($item->checked_out)
						    {
						        JHtml::_('dropdown.checkin', 'cb' . $i, 'attachments.');
						    }

						    if ($trashed)
						    {
						        JHtml::_('dropdown.untrash', 'cb' . $i, 'attachments.');
						    }
						    else
						    {
						        JHtml::_('dropdown.trash', 'cb' . $i, 'attachments.');
						    }

						    echo JHtml::_('dropdown.render');
						    ?>
						</div>
						</td>
						<td class="small hidden-phone">
						<?php echo $this->escape($item->access_level); ?>
					</td>
					<td class="small hidden-phone">
							<a
							href="<?php echo JRoute::_('index.php?option=com_users&task=user.edit&id=' . (int) $item->created_by); ?>"
							title="<?php echo JText::_('JAUTHOR'); ?>">
							<?php echo $this->escape($item->author_name); ?></a>
					</td>
					<td class="nowrap small hidden-phone">
						<?php echo JHtml::_('date', $item->created, JText::_('DATE_FORMAT_LC4')); ?>
					</td>
					<td class="center">
						<?php echo (int) $item->hits; ?>
					</td>
					<td class="center hidden-phone">
						<?php echo (int) $item->id; ?>
					</td>
					</tr>
				<?php
				} ?>
			</tbody>
			</table>
		<?php echo $this->pagination->getListFooter(); ?>
		<?php echo $this->loadTemplate('batch'); ?>

		<input type="hidden" name="task" value="" /> <input type="hidden"
				name="boxchecked" value="0" /> <input type="hidden"
				name="filter_order" value="<?php echo $listOrder; ?>" /> <input
				type="hidden" name="filter_order_Dir"
				value="<?php echo $listDirn; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
