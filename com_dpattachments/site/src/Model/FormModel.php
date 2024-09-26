<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2013 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

namespace DigitalPeak\Component\DPAttachments\Site\Model;

use DigitalPeak\Component\DPAttachments\Administrator\Model\AttachmentModel;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\Registry\Registry;

class FormModel extends AttachmentModel
{
	protected function populateState()
	{
		$app = Factory::getApplication();
		if (!$app instanceof CMSApplication) {
			return;
		}

		// Load state from the request.
		$pk = $app->input->getInt('id', 0);
		$this->setState('attachment.id', $pk);

		$return = $app->input->get('return', null, 'base64');
		$this->setState('return_page', base64_decode((string)$return));

		// Load the parameters
		$params = ComponentHelper::getParams('com_dpattachments');
		if ($app instanceof SiteApplication) {
			$params = $app->getParams();
		}
		$this->setState('params', $params);

		$this->setState('layout', $app->input->get('layout'));
	}

	public function getItem($pk = null)
	{
		$pk = (int)(!empty($pk)) !== 0 ? $pk : $this->getState('attachment.id');

		$item = parent::getItem($pk);
		if (!\is_object($item)) {
			return $item;
		}

		$item->params = new Registry($item->params);

		// Check edit state permission.
		if ($pk) {
			$item->params->set('access-edit', Factory::getApplication()->bootComponent('dpattachments')->canDo('core.edit', $item->context, $item->item_id));
			$item->params->set('access-change', Factory::getApplication()->bootComponent('dpattachments')->canDo('core.edit.state', $item->context, $item->item_id));
		}

		return $item;
	}

	public function getReturnPage(): string
	{
		return base64_encode((string)$this->getState('return_page'));
	}
}
