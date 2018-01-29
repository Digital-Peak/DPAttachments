<?php
/**
 * @package    DPAttachments
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2012 - 2018 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class DPAttachmentsController extends JControllerLegacy
{

	protected $default_view = 'attachments';

	public function display ($cachable = false, $urlparams = false)
	{
		$view = $this->input->get('view', 'attachments');
		$layout = $this->input->get('layout', 'attachments');
		$id = $this->input->getInt('id');

		// Check for edit form.
		if ($view == 'attachment' && $layout == 'edit' && ! $this->checkEditId('com_dpattachments.edit.attachment', $id))
		{
			// Somehow the person just went to the form - we don't allow that.
			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id));
			$this->setMessage($this->getError(), 'error');
			$this->setRedirect(JRoute::_('index.php?option=com_dpattachments&view=attachments', false));

			return false;
		}

		parent::display();

		return $this;
	}
}
