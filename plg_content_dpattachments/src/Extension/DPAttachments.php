<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2013 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace DigitalPeak\Plugin\Content\DPAttachments\Extension;

use DigitalPeak\Component\DPAttachments\Administrator\Extension\DPAttachmentsComponent;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Registry\Registry;

class DPAttachments extends CMSPlugin
{
	/** @var CMSApplication $app */
	protected $app;

	protected $autoloadLanguage = true;

	public function onContentAfterDisplay($context, $item, $params)
	{
		// Ignore DPAttachments
		if ($context === 'com_dpattachments.attachment') {
			return;
		}

		// Check if there is an ID
		if (empty($item->id)) {
			return '';
		}

		// Filter by category ids
		$catIds = $this->params->get('cat_ids');
		if (isset($item->catid) && !empty($catIds) && !in_array($item->catid, $catIds)) {
			return '';
		}

		// Mak the correct context
		if ($context === 'com_content.featured') {
			$context = 'com_content.article';
		}

		// Get the component instance
		$component = $this->app->bootComponent('dpattachments');
		if (!$component instanceof DPAttachmentsComponent) {
			return;
		}

		// Render the attachments and upload form
		$buffer = $component->render($context, $item->id, new Registry(['render.columns' => $this->params->get('column_count', 2)]));

		// Render the attachment form the original event as well
		if (isset($item->original_id) && $item->original_id > 0) {
			$buffer .= $component->render($context, $item->original_id, new Registry(['render.columns' => $this->params->get('column_count', 2)]), false);
		}

		return $buffer;
	}

	public function onContentPrepareForm(Form $form, $data)
	{
		// The path to the form XML files
		$formsFolderPath = JPATH_PLUGINS . '/' . $this->_type . '/' . $this->_name . '/forms';

		// The context
		$context = $form->getName();

		// The component instance
		$component = $this->app->bootComponent('dpattachments');
		if (!$component instanceof DPAttachmentsComponent) {
			return;
		}

		// Check if there are categories to filter
		$catIds = $this->params->get('cat_ids');
		if (isset($data->catid) && !empty($catIds) && !in_array($data->catid, $catIds)) {
			return '';
		}

		// Map the context
		if ($context === 'com_content.featured') {
			$context = 'com_content.article';
		}

		// Load the attachments into the form
		$form->loadFile($formsFolderPath . '/attachments.xml');

		// Set the item ID attribute
		$form->setFieldAttribute(
			'attachments',
			'item_id',
			is_object($data) && !empty($data->id) ? $data->id : (is_array($data) && !empty($data['id']) ? $data['id'] : 0)
		);
	}

	public function onContentAfterDelete($context, $item)
	{
		// Check if there is an id
		if (empty($item->id)) {
			return '';
		}

		// Map the context
		if ($context === 'com_content.featured') {
			$context = 'com_content.article';
		}

		// Load the component instance
		$component = $this->app->bootComponent('dpattachments');
		if (!$component instanceof DPAttachmentsComponent) {
			return;
		}

		// Delete the attachment for the item
		return $component->delete($context, $item->id);
	}
}