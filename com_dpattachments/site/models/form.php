<?php
/**
 * @package    DPAttachments
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2012 - 2018 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

JLoader::import('components.com_dpattachments.models.attachment', JPATH_ADMINISTRATOR);

class DPAttachmentsModelForm extends DPAttachmentsModelAttachment
{

	protected function populateState ()
	{
		$app = JFactory::getApplication();

		// Load state from the request.
		$pk = $app->input->getInt('a_id');
		$this->setState('attachment.id', $pk);

		$return = $app->input->get('return', null, 'base64');
		$this->setState('return_page', base64_decode($return));

		// Load the parameters.
		$params = $app->getParams();
		$this->setState('params', $params);

		$this->setState('layout', $app->input->get('layout'));
	}

	public function getItem ($itemId = null)
	{
		$itemId = (int) (! empty($itemId)) ? $itemId : $this->getState('attachment.id');

		// Get a row instance.
		$table = $this->getTable();

		// Attempt to load the row.
		$return = $table->load($itemId);

		// Check for a table object error.
		if ($return === false && $table->getError())
		{
			$this->setError($table->getError());
			return false;
		}

		$properties = $table->getProperties(1);
		$value = JArrayHelper::toObject($properties, 'JObject');

		// Convert attrib field to Registry.
		$value->params = new JRegistry();

		// Compute selected asset permissions.
		$user = JFactory::getUser();

		// Check edit state permission.
		if ($itemId)
		{
			$value->params->set('access-edit', \DPAttachments\Helper\Core::canDo('core.edit', $value->context, $value->item_id));
			$value->params->set('access-change', \DPAttachments\Helper\Core::canDo('core.edit.state', $value->context, $value->item_id));
		}

		return $value;
	}

	public function getReturnPage ()
	{
		return base64_encode($this->getState('return_page'));
	}
}
