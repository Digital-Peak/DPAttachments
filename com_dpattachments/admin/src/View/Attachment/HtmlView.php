<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2013 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

namespace DigitalPeak\Component\DPAttachments\Administrator\View\Attachment;

use DigitalPeak\Component\DPAttachments\Administrator\Helper\DPAttachmentsHelper;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Input\Input;
use Joomla\Registry\Registry;

class HtmlView extends BaseHtmlView
{
	/** @var Form */
	protected $form;

	/** @var \stdClass */
	protected $item;

	/** @var Registry */
	protected $state;

	/** @var Input */
	protected $input;

	public function display($tpl = null): void
	{
		$this->form  = $this->getModel()->getForm();
		$this->item  = $this->getModel()->getItem() ?: new \stdClass();
		$this->state = $this->getModel()->getState();

		$app = Factory::getApplication();
		if ($app instanceof CMSApplication) {
			$this->input = $app->getInput();
		}

		// Check for errors.
		if ($errors = $this->getModel()->getErrors()) {
			throw new \Exception(implode(',', $errors));
		}

		$this->addToolbar();

		parent::display($tpl);
	}

	private function addToolbar(): void
	{
		$this->input->set('hidemainmenu', true);

		$user       = $this->getCurrentUser();
		$isNew      = ($this->item->id == 0);
		$checkedOut = $this->item->checked_out != 0 && $this->item->checked_out != $user->id;
		$canDo      = DPAttachmentsHelper::getActions($user);
		ToolbarHelper::title(
			Text::_('COM_DPATTACHMENTS_VIEW_ATTACHMENT_' . ($checkedOut ? 'ATTACHMENT' : ($isNew ? 'ADD_ATTACHMENT' : 'EDIT_ATTACHMENT')))
		);

		// Built the actions for new and existing records.

		// For new records, check the create permission.
		if ($isNew && (\count($user->getAuthorisedCategories('com_dpattachments', 'core.create')) > 0)) {
			ToolbarHelper::apply('attachment.apply');
			ToolbarHelper::save('attachment.save');
			ToolbarHelper::cancel('attachment.cancel');

			return;
		}

		// Can't save the record if it's checked out.
		// Since it's an existing record, check the edit permission, or
		// fall back to edit own if the owner.
		if (!$checkedOut && ($canDo->get('core.edit') || ($canDo->get('core.edit.own') && $this->item->created_by == $user->id))) {
			ToolbarHelper::apply('attachment.apply');
			ToolbarHelper::save('attachment.save');
		}

		ToolbarHelper::cancel('attachment.cancel', 'JTOOLBAR_CLOSE');
	}
}
