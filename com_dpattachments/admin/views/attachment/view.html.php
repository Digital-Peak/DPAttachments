<?php
/**
 * @package    DPAttachments
 * @copyright  (C) 2013 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class DPAttachmentsViewAttachment extends JViewLegacy
{
	protected $form;
	protected $item;
	protected $state;

	public function display($tpl = null)
	{
		$this->form  = $this->get('Form');
		$this->item  = $this->get('Item');
		$this->state = $this->get('State');
		$this->canDo = \DPAttachments\Helper\DPAttachmentsHelper::getActions();

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}

		$this->addToolbar();
		parent::display($tpl);
	}

	protected function addToolbar()
	{
		JFactory::getApplication()->input->set('hidemainmenu', true);
		$user       = JFactory::getUser();
		$userId     = $user->get('id');
		$isNew      = ($this->item->id == 0);
		$checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $userId);
		$canDo      = \DPAttachments\Helper\DPAttachmentsHelper::getActions();
		JToolbarHelper::title(
			JText::_('COM_DPATTACHMENTS_VIEW_ATTACHMENT_' . ($checkedOut ? 'ATTACHMENT' : ($isNew ? 'ADD_ATTACHMENT' : 'EDIT_ATTACHMENT')))
		);

		// Built the actions for new and existing records.

		// For new records, check the create permission.
		if ($isNew && (count($user->getAuthorisedCategories('com_dpattachments', 'core.create')) > 0)) {
			JToolbarHelper::apply('attachment.apply');
			JToolbarHelper::save('attachment.save');
			JToolbarHelper::save2new('attachment.save2new');
			JToolbarHelper::cancel('attachment.cancel');
		} else {
			// Can't save the record if it's checked out.
			if (!$checkedOut) {
				// Since it's an existing record, check the edit permission, or
				// fall back to edit own if the owner.
				if ($canDo->get('core.edit') || ($canDo->get('core.edit.own') && $this->item->created_by == $userId)) {
					JToolbarHelper::apply('attachment.apply');
					JToolbarHelper::save('attachment.save');

					// We can save this record, but check the create permission
					// to see if we can return to make a new one.
					if ($canDo->get('core.create')) {
						JToolbarHelper::save2new('attachment.save2new');
					}
				}
			}

			// If checked out, we can still save
			if ($canDo->get('core.create')) {
				JToolbarHelper::save2copy('attachment.save2copy');
			}

			JToolbarHelper::cancel('attachment.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}
