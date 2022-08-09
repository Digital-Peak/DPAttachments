<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2013 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace DigitalPeak\Component\DPAttachments\Administrator\Controller;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;

class DisplayController extends BaseController
{
	protected $default_view = 'attachments';

	public function display($cachable = false, $urlparams = false)
	{
		$view   = $this->input->get('view', 'attachments');
		$layout = $this->input->get('layout', 'attachments');
		$id     = $this->input->getInt('id');

		// Check for edit form.
		if ($view == 'attachment' && $layout == 'edit' && !$this->checkEditId('com_dpattachments.edit.attachment', $id)) {
			// Somehow the person just went to the form - we don't allow that
			$this->setMessage(Text::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id), 'error');
			$this->setRedirect(Route::_('index.php?option=com_dpattachments&view=attachments', false));

			return false;
		}

		parent::display();

		return $this;
	}
}
