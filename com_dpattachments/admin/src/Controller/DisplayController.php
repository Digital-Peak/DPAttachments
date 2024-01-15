<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2013 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

namespace DigitalPeak\Component\DPAttachments\Administrator\Controller;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;

class DisplayController extends BaseController
{
	public $input;
	protected $default_view = 'attachments';

	public function display($cachable = false, $urlparams = []): self
	{
		$view   = $this->input->get('view', 'attachments');
		$layout = $this->input->get('layout', 'attachments');
		$id     = $this->input->getInt('id', 0);

		// Check for edit form.
		if ($view == 'attachment' && $layout == 'edit' && !$this->checkEditId('com_dpattachments.edit.attachment', $id)) {
			// Somehow the person just went to the form - we don't allow that
			$this->setMessage(Text::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id), 'error');
			$this->setRedirect(Route::_('index.php?option=com_dpattachments&view=attachments', false));

			return $this;
		}

		parent::display();

		return $this;
	}
}
