<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2013 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace DigitalPeak\Plugin\Content\DPAttachments\Extension;

use DigitalPeak\Component\DPAttachments\Administrator\Extension\DPAttachmentsComponent;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Component\ComponentHelper;
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

		// Make the correct context
		if ($context === 'com_content.featured') {
			$context = 'com_content.article';
		}

		// Get the component instance
		$component = $this->app->bootComponent('dpattachments');
		if (!$component instanceof DPAttachmentsComponent) {
			return;
		}

		// Render the attachments and upload form
		$buffer = $component->render(
			$context,
			$item->id,
			new Registry(['render.columns' => $this->params->get('column_count', 2), 'item' => $item])
		);

		// Render the attachment form the original event as well
		if (isset($item->original_id) && $item->original_id > 0) {
			$buffer .= $component->render(
				$context,
				$item->original_id,
				new Registry(['render.columns' => $this->params->get('column_count', 2), 'item' => $item]),
				false
			);
		}

		return $buffer;
	}

	public function onContentPrepareForm(Form $form, $data)
	{
		// The path to the form XML files
		$formsFolderPath = JPATH_PLUGINS . '/' . $this->_type . '/' . $this->_name . '/forms';

		// The context
		$context = $form->getName();

		// When DPAttachments
		if ($context === 'com_dpattachments.attachment') {
			$dataContext = '';
			if (is_object($data) && !empty($data->context)) {
				$dataContext = $data->context;
			}

			if (!$dataContext && is_array($data) && !empty($data['context'])) {
				$dataContext = $data['context'];
			}

			$inputData = $this->app->input->get('jform', [], 'array');
			if (!$dataContext && $inputData && !empty($inputData['context'])) {
				$dataContext = $inputData['context'];
			}

			// Add some extension specific fields
			if ($dataContext && file_exists($formsFolderPath . '/context/' . $dataContext . '.xml')) {
				$form->loadFile($formsFolderPath . '/context/' . $dataContext . '.xml');
			}

			return;
		}

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

		list($componentName) = explode('.', $context);
		$params              = ComponentHelper::getParams('com_dpattachments');

		// Check if the component is in the list of excluded ones
		$components = $params->get('components_exclude', ['com_plugins', 'com_config', 'com_menus']);
		if (!empty($components)) {
			if (!is_array($components)) {
				$components = [$components];
			}

			if (in_array($componentName, $components)) {
				return;
			}
		}

		// Check if the component is in the list of the selected ones
		$components = $params->get('components');
		if (!empty($components)) {
			if (!is_array($components)) {
				$components = [$components];
			}

			if (!in_array($componentName, $components)) {
				return;
			}
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
