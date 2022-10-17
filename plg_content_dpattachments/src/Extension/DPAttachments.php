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
		$context = $this->transformContext($context, $item);

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

		return $buffer;
	}

	public function onContentPrepareForm(Form $form, $data)
	{
		$context = $this->transformContext($form->getName(), $data);

		if ($context === 'com_dpattachments.attachment') {
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

		list($componentName) = explode('.', $context);
		$params              = ComponentHelper::getParams('com_dpattachments');

		// Check if the component is in the list of excluded ones
		$components = $params->get('components_exclude', ['com_plugins', 'com_config', 'com_menus', 'com_scheduler']);
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
		$form->loadFile(JPATH_PLUGINS . '/content/dpattachments/forms/attachments.xml');

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

		// Load the component instance
		$component = $this->app->bootComponent('dpattachments');
		if (!$component instanceof DPAttachmentsComponent) {
			return;
		}

		// Delete the attachment for the item
		return $component->delete($this->transformContext($context, $item), $item->id);
	}

	/**
	 * Transforms the given context for the given item into a default one. Like
	 * that we ensure the same context across different views for the same entity.
	 *
	 * @param string $context
	 * @param mixed  $item
	 *
	 * @return string
	 */
	private function transformContext(string $context, $item)
	{
		// Categories lists
		if ($context === 'com_content.categories') {
			return 'com_categories.category';
		}

		// Featured display
		if ($context === 'com_content.featured') {
			return 'com_content.article';
		}

		// Items in a category list view
		if ($context === 'com_content.category' && !empty($item->catid)) {
			return 'com_content.article';
		}

		return $context;
	}
}
