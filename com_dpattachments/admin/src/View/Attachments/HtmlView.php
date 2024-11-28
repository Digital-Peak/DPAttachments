<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2013 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

namespace DigitalPeak\Component\DPAttachments\Administrator\View\Attachments;

use DigitalPeak\Component\DPAttachments\Administrator\Helper\DPAttachmentsHelper;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Registry\Registry;

class HtmlView extends BaseHtmlView
{
	/** @var Form */
	public $filterForm;

	/** @var array */
	protected $authors;

	/** @var array */
	protected $activeFilters;

	/** @var Pagination */
	protected $pagination;

	/** @var Registry */
	protected $canDo;

	/** @var array */
	protected $items;

	/** @var Registry */
	protected $state;

	public function display($tpl = null): void
	{
		$this->items      = $this->getModel()->getItems();
		$this->pagination = $this->getModel()->getPagination();
		$this->state      = $this->getModel()->getState();
		$this->authors    = $this->getModel()->getAuthors();

		// Check for errors.
		if ($errors = $this->getModel()->getErrors()) {
			throw new \Exception(implode('\n', $errors));
		}

		$this->addToolbar();

		parent::display($tpl);
	}

	private function addToolbar(): void
	{
		$canDo = DPAttachmentsHelper::getActions($this->getCurrentUser());

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

		if ($canDo->get('core.admin')) {
			ToolbarHelper::preferences('com_dpattachments');
		}

		$this->filterForm    = $this->getModel()->getFilterForm();
		$this->activeFilters = $this->getModel()->getActiveFilters();
	}

	public function renderContext(string $context): string
	{
		$context = str_replace('com_', '', strtolower($context));

		$buffer = '';
		foreach (explode('.', $context) as $part) {
			$buffer .= ucfirst($part) . ' ';
		}

		return trim($buffer, ' ');
	}
}
