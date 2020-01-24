<?php
/**
 * @package    DPAttachments
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2012 - 2020 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class DPAttachmentsViewAttachments extends JViewLegacy
{
	protected $items;
	protected $pagination;
	protected $state;

	public function display($tpl = null)
	{
		\DPAttachments\Helper\DPAttachmentsHelper::addSubmenu('attachments');

		$this->items      = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->state      = $this->get('State');
		$this->authors    = $this->get('Authors');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}

		$this->addToolbar();
		$this->sidebar = JHtmlSidebar::render();

		parent::display($tpl);
	}

	protected function addToolbar()
	{
		$canDo = \DPAttachments\Helper\DPAttachmentsHelper::getActions();
		$user  = JFactory::getUser();

		// Get the toolbar object instance
		$bar = JToolBar::getInstance('toolbar');

		JToolbarHelper::title(JText::_('COM_DPATTACHMENTS_VIEW_ATTACHMENTS_TITLE'));

		if ($canDo->get('core.create')) {
			JToolbarHelper::addNew('attachment.add');
		}

		if (($canDo->get('core.edit')) || ($canDo->get('core.edit.own'))) {
			JToolbarHelper::editList('attachment.edit');
		}

		if ($canDo->get('core.edit.state')) {
			JToolbarHelper::publish('attachments.publish', 'JTOOLBAR_PUBLISH', true);
			JToolbarHelper::unpublish('attachments.unpublish', 'JTOOLBAR_UNPUBLISH', true);
			JToolbarHelper::archiveList('attachments.archive');
			JToolbarHelper::checkin('attachments.checkin');
		}

		if ($this->state->get('filter.published') == -2 && $canDo->get('core.delete')) {
			JToolbarHelper::deleteList('', 'attachments.delete', 'JTOOLBAR_EMPTY_TRASH');
		} elseif ($canDo->get('core.edit.state')) {
			JToolbarHelper::trash('attachments.trash');
		}

		// Add a batch button
		$asset = 'com_dpattachments';
		if ($user->authorise('core.create', $asset) && $user->authorise('core.edit', $asset) && $user->authorise('core.edit.state', $asset)) {
			JHtml::_('bootstrap.modal', 'collapseModal');
			$title = JText::_('JTOOLBAR_BATCH');

			// Instantiate a new JLayoutFile instance and render the batch
			// button
			$layout = new JLayoutFile('joomla.toolbar.batch');

			$dhtml = $layout->render(array(
				'title' => $title
			));
			$bar->appendButton('Custom', $dhtml, 'batch');
		}

		if ($canDo->get('core.admin')) {
			JToolbarHelper::preferences('com_dpattachments');
		}

		JHtmlSidebar::setAction('index.php?option=com_dpattachments&view=attachments');

		JHtmlSidebar::addFilter(JText::_('JOPTION_SELECT_PUBLISHED'), 'filter_published',
			JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true));
		JHtmlSidebar::addFilter(JText::_('JOPTION_SELECT_ACCESS'), 'filter_access',
			JHtml::_('select.options', JHtml::_('access.assetgroups'), 'value', 'text', $this->state->get('filter.access')));
		JHtmlSidebar::addFilter(JText::_('JOPTION_SELECT_AUTHOR'), 'filter_author_id',
			JHtml::_('select.options', $this->authors, 'value', 'text', $this->state->get('filter.author_id')));
	}

	protected function getSortFields()
	{
		return array(
			'a.ordering'     => JText::_('JGRID_HEADING_ORDERING'),
			'a.state'        => JText::_('JSTATUS'),
			'a.title'        => JText::_('JGLOBAL_TITLE'),
			'category_title' => JText::_('JCATEGORY'),
			'access_level'   => JText::_('JGRID_HEADING_ACCESS'),
			'a.created_by'   => JText::_('JAUTHOR'),
			'language'       => JText::_('JGRID_HEADING_LANGUAGE'),
			'a.created'      => JText::_('JDATE'),
			'a.id'           => JText::_('JGRID_HEADING_ID'),
			'a.featured'     => JText::_('JFEATURED')
		);
	}
}
