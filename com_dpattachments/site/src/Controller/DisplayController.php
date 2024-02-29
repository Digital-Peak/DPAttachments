<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2013 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

namespace DigitalPeak\Component\DPAttachments\Site\Controller;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\User\CurrentUserInterface;
use Joomla\CMS\User\CurrentUserTrait;

class DisplayController extends BaseController implements CurrentUserInterface
{
	use CurrentUserTrait;

	public function display($cachable = false, $urlparams = []): self
	{
		$cachable = true;

		$id    = $this->input->getInt('id', 0);
		$vName = $this->input->getCmd('view', '');
		$this->input->set('view', $vName);

		$user = $this->getCurrentUser();

		if ($user->id || $this->input->getMethod() == 'POST') {
			$cachable = false;
		}

		$safeurlparams = [
			'id'               => 'INT',
			'cid'              => 'ARRAY',
			'year'             => 'INT',
			'month'            => 'INT',
			'limit'            => 'UINT',
			'limitstart'       => 'UINT',
			'showall'          => 'INT',
			'return'           => 'BASE64',
			'filter'           => 'STRING',
			'filter_order'     => 'CMD',
			'filter_order_Dir' => 'CMD',
			'filter-search'    => 'STRING',
			'print'            => 'BOOLEAN',
			'lang'             => 'CMD',
			'Itemid'           => 'INT'
		];

		// Check for edit form.
		if ($vName == 'form' && !$this->checkEditId('com_dpattachments.edit.attachment', $id)) {
			// Somehow the person just went to the form - we don't allow that.
			throw new \Exception(Text::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id), 403);
		}

		parent::display($cachable, $safeurlparams);

		return $this;
	}
}
