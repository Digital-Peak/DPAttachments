<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2013 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

namespace DigitalPeak\Component\DPAttachments\Site\Controller;

use DigitalPeak\Component\DPAttachments\Administrator\Controller\AttachmentController as BaseAttachmentController;
use Joomla\CMS\Uri\Uri;

class AttachmentController extends BaseAttachmentController
{
	protected $view_item = 'form';

	public function cancel($key = 'id')
	{
		$result = parent::cancel($key);

		$this->setRedirect($this->getReturnPage());

		return $result;
	}

	public function save($key = null, $urlVar = 'id')
	{
		$result = parent::save($key, $urlVar);

		// If ok, redirect to the return page.
		if ($result) {
			$this->setRedirect($this->getReturnPage());
		}

		return $result;
	}

	public function publish(): void
	{
		parent::publish();

		$this->setRedirect($this->getReturnPage());
	}

	protected function getRedirectToItemAppend($recordId = null, $urlVar = 'id'): string
	{
		$tmpl   = $this->input->get('tmpl');
		$append = '';

		if ($tmpl) {
			$append .= '&tmpl=' . $tmpl;
		}

		$append .= '&layout=edit';

		if ($recordId) {
			$append .= '&' . $urlVar . '=' . $recordId;
		}

		$itemId = $this->input->getInt('Itemid', 0);
		$return = $this->getReturnPage();

		if ($itemId !== 0) {
			$append .= '&Itemid=' . $itemId;
		}

		if ($return !== '' && $return !== '0') {
			$append .= '&return=' . base64_encode($return . ($tmpl ? (str_contains($return, '?') ? '&' : '?') . 'tmpl=' . $tmpl : ''));
		}

		return $append;
	}

	protected function getReturnPage(): string
	{
		$return = $this->input->get('return', null, 'base64');
		if (empty($return) || !Uri::isInternal(base64_decode((string)$return))) {
			return Uri::base();
		}

		return base64_decode((string)$return);
	}

	public function getModel($name = 'Form', $prefix = 'Site', $config = ['ignore_request' => true])
	{
		return parent::getModel($name, $prefix, $config);
	}
}
