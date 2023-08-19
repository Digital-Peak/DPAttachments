<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2013 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

namespace DigitalPeak\Component\DPAttachments\Site\Model;

use DigitalPeak\Component\DPAttachments\Administrator\Model\AttachmentModel;
use Joomla\CMS\Factory;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

class FormModel extends AttachmentModel
{
	protected function populateState()
	{
		$app = Factory::getApplication();

		// Load state from the request.
		$pk = $app->input->getInt('id');
		$this->setState('attachment.id', $pk);

		$return = $app->input->get('return', null, 'base64');
		$this->setState('return_page', base64_decode($return));

		// Load the parameters.
		$params = $app->getParams();
		$this->setState('params', $params);

		$this->setState('layout', $app->input->get('layout'));
	}

	public function getItem($itemId = null)
	{
		$itemId = (int)(!empty($itemId)) ? $itemId : $this->getState('attachment.id');

		// Get a row instance.
		$table = $this->getTable();

		// Attempt to load the row.
		$return = $table->load($itemId);

		// Check for a table object error.
		if ($return === false && $table->getError()) {
			$this->setError($table->getError());

			return false;
		}

		$properties = $table->getProperties(1);
		$value      = ArrayHelper::toObject($properties);

		// Convert attrib field to Registry.
		$value->params = new Registry($value->params);

		// Check edit state permission.
		if ($itemId) {
			$value->params->set('access-edit', Factory::getApplication()->bootComponent('dpattachments')->canDo('core.edit', $value->context, $value->item_id));
			$value->params->set('access-change', Factory::getApplication()->bootComponent('dpattachments')->canDo('core.edit.state', $value->context, $value->item_id));
		}

		return $value;
	}

	public function getReturnPage()
	{
		return base64_encode($this->getState('return_page'));
	}
}
