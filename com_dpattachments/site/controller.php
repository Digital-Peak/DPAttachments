<?php
/**
 * @package    DPAttachments
 * @copyright  (C) 2013 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class DPAttachmentsController extends JControllerLegacy
{

	public function display($cachable = false, $urlparams = false)
	{
		$cachable = true;

		$id    = $this->input->getInt('a_id');
		$vName = $this->input->getCmd('view', '');
		$this->input->set('view', $vName);

		$user = JFactory::getUser();

		if ($user->get('id') || $this->input->getMethod() == 'POST') {
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
			return JError::raiseError(403, JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id));
		}

		parent::display($cachable, $safeurlparams);

		return $this;
	}
}
