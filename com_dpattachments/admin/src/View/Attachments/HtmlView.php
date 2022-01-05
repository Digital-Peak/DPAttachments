<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2013 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace DigitalPeak\Component\DPAttachments\Administrator\View\Attachments;

defined('_JEXEC') or die();

use DigitalPeak\Component\DPAttachments\Administrator\Helper\DPAttachmentsHelper;
use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

class HtmlView extends BaseHtmlView
{
	protected $items;
	protected $pagination;
	protected $state;

	public function display($tpl = null)
	{
		$this->items      = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->state      = $this->get('State');
		$this->authors    = $this->get('Authors');

		// Check for errors.
		if ($errors = $this->get('Errors')) {
			throw new Exception(implode('\n', $errors));
		}

		$this->addToolbar();

		parent::display($tpl);
	}

	protected function addToolbar()
	{
		$canDo = DPAttachmentsHelper::getActions();
		$user  = Factory::getUser();

		// Get the toolbar object instance
		$bar = Toolbar::getInstance('toolbar');

		ToolbarHelper::title(Text::_('COM_DPATTACHMENTS_VIEW_ATTACHMENTS_TITLE'));

		if ($canDo->get('core.edit') || $canDo->get('core.edit.own')) {
			ToolbarHelper::editList('attachment.edit');
		}

		if ($canDo->get('core.edit.state')) {
			ToolbarHelper::publish('attachments.publish', 'JTOOLBAR_PUBLISH', true);
			ToolbarHelper::unpublish('attachments.unpublish', 'JTOOLBAR_UNPUBLISH', true);
			ToolbarHelper::archiveList('attachments.archive');
			ToolbarHelper::checkin('attachments.checkin');
		}

		if ($this->state->get('filter.state') == -2 && $canDo->get('core.delete')) {
			ToolbarHelper::deleteList('', 'attachments.delete', 'JTOOLBAR_EMPTY_TRASH');
		} elseif ($canDo->get('core.edit.state')) {
			ToolbarHelper::trash('attachments.trash');
		}

		// Add a batch button
		$asset = 'com_dpattachments';
		if ($user->authorise('core.create', $asset) && $user->authorise('core.edit', $asset) && $user->authorise('core.edit.state', $asset) && version_compare(JVERSION, '4', '<')) {
			HTMLHelper::_('bootstrap.modal', 'collapseModal');
			$title = Text::_('JTOOLBAR_BATCH');

			// Instantiate a new JLayoutFile instance and render the batch button
			$layout = new FileLayout('joomla.toolbar.batch');

			$bar->appendButton('Custom', $layout->render(['title' => $title]), 'batch');
		}

		if ($canDo->get('core.admin')) {
			ToolbarHelper::preferences('com_dpattachments');
		}

		// Only render the sidebar when we are not editing a form, modal or Joomla 4
		if (version_compare(JVERSION, '4', '<')) {
			$this->sidebar = \JHtmlSidebar::render();
		} else {
			$this->sidebar = null;
		}
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');
	}

	public function renderContext($context)
	{
		$context = str_replace('com_', '', strtolower($context));

		$buffer = '';
		foreach (explode('.', $context) as $part) {
			$buffer .= ucfirst($part) . ' ';
		}

		return trim($buffer, ' ');
	}
}
