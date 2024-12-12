<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2013 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

namespace DigitalPeak\Plugin\Content\DPAttachments\Extension;

use DigitalPeak\Component\DPAttachments\Administrator\Model\AttachmentsModel;
use Joomla\CMS\Application\CMSWebApplicationInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Event\Content\AfterDisplayEvent;
use Joomla\CMS\Event\Model\AfterSaveEvent;
use Joomla\CMS\Event\Model\PrepareFormEvent;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;
use Joomla\Registry\Registry;

class DPAttachments extends CMSPlugin implements SubscriberInterface
{
	public static function getSubscribedEvents(): array
	{
		return [
			'onContentAfterDisplay' => 'render',
			'onContentPrepareForm'  => 'prepareForm',
			'onContentAfterSave'    => 'reassign',
			'onContentAfterDelete'  => 'cleanup'
		];
	}

	protected $autoloadLanguage = true;

	private array $FORMS_TO_EXCLUDE = ['com_users.registration'];

	public function render(Event $event): void
	{
		$context = $event->getArgument($event instanceof AfterDisplayEvent ? 'context' : '0');
		$item    = $event->getArgument($event instanceof AfterDisplayEvent ? 'subject' : '1');

		$context = $this->transformContext($context, $item);

		// Ignore DPAttachments
		if ($context === 'com_dpattachments.attachment') {
			return;
		}

		// Check if there is an ID
		if (empty($item->id)) {
			return;
		}

		// Filter by category ids
		$catIds = $this->params->get('cat_ids');
		if (isset($item->catid) && !empty($catIds) && !\in_array($item->catid, $catIds)) {
			return;
		}

		$app = $this->getApplication();
		if (!$app instanceof CMSWebApplicationInterface) {
			return;
		}

		// Render the attachments and upload form
		$event->setArgument(
			'result',
			array_merge($event->getArgument('result'), [$app->bootComponent('dpattachments')->render(
				$context,
				$item->id,
				new Registry(['render.columns' => $this->params->get('column_count', 2), 'item' => $item])
			)])
		);
	}

	public function prepareForm(Event $event): void
	{
		$form = $event->getArgument($event instanceof PrepareFormEvent ? 'form' : '0');
		$data = $event->getArgument($event instanceof PrepareFormEvent ? 'data' : '1');

		$context = $this->transformContext($form->getName(), $data);

		// Do not load it on our own
		if ($context === 'com_dpattachments.attachment') {
			return;
		}

		// Check if the context belongs to a form we don't want
		if (\in_array($context, $this->FORMS_TO_EXCLUDE)) {
			return;
		}

		$app = $this->getApplication();
		if (!$app instanceof CMSWebApplicationInterface) {
			return;
		}

		// Check if there are categories to filter
		$catIds = $this->params->get('cat_ids');
		if (!empty($catIds) && isset($data->catid) && !\in_array($data->catid, $catIds)) {
			return;
		}

		[$componentName] = explode('.', $context);
		$params          = ComponentHelper::getParams('com_dpattachments');

		// Check if the component is in the list of excluded ones
		$components = $params->get(
			'components_exclude',
			['com_cache', 'com_actionlogs', 'com_menus', 'com_config', 'com_scheduler', 'com_plugins', 'com_media']
		);
		if (!empty($components) && \in_array($componentName, (array)$components)) {
			return;
		}

		// Check if the component is in the list of the selected ones
		$components = $params->get('components');
		if (!empty($components) && !\in_array($componentName, (array)$components)) {
			return;
		}

		// Load the attachments into the form
		$form->loadFile(JPATH_PLUGINS . '/content/dpattachments/forms/attachments.xml');

		// Set the temporary ID from the session
		$id = 'tmp-' . $app->getSession()->getId();

		// Determine the id when it is an object
		if (\is_object($data) && !empty($data->id)) {
			$id = $data->id;
		}

		// Determine the id when it is an array
		if (\is_array($data) && !empty($data['id'])) {
			$id = $data['id'];
		}

		// Set the item ID attribute
		$form->setFieldAttribute('attachments', 'item_id', $id);
	}

	public function reassign(Event $event): void
	{
		// Get the arguments
		$context = $event->getArgument($event instanceof AfterSaveEvent ? 'context' : '0');
		$item    = $event->getArgument($event instanceof AfterSaveEvent ? 'subject' : '1');
		$isNew   = $event->getArgument($event instanceof AfterSaveEvent ? 'isNew' : '2');

		if (!$isNew) {
			return;
		}

		$app = $this->getApplication();
		if (!$app instanceof CMSWebApplicationInterface) {
			return;
		}

		// Load the component instance
		$component = $app->bootComponent('dpattachments');

		$context = $this->transformContext($context, $item);

		/** @var AttachmentsModel $model */
		$model = $component->getMVCFactory()->createModel('Attachments', 'Administrator', ['ignore_request' => true]);
		$model->getState();
		$model->setState('filter.item', 'tmp-' . $app->getSession()->getId());
		$model->setState('filter.context', $context);
		$model->setState('filter.state', 1);
		$model->setState('list.limit', 1000);
		$model->setState('list.start', 0);

		// Loop through all attachments of the current session
		foreach ($model->getItems() as $attachment) {
			// Load the table with the temporary id
			$table = $component->getMVCFactory()->createTable('Attachment', 'Administrator');
			$table->load($attachment->id);

			// Set the real ID of the item
			$table->item_id = $item->id;

			// Save it back to the database
			$table->store();
		}
	}

	public function cleanup(Event $event): void
	{
		// Check if there is an id
		// Get the arguments
		$context = $event->getArgument($event instanceof AfterSaveEvent ? 'context' : '0');
		$item    = $event->getArgument($event instanceof AfterSaveEvent ? 'subject' : '1');

		if (empty($item->id)) {
			return;
		}

		$app = $this->getApplication();
		if (!$app instanceof CMSWebApplicationInterface) {
			return;
		}

		// Delete the attachment for the item
		$app->bootComponent('dpattachments')->delete($this->transformContext($context, $item), $item->id);
	}

	/**
	 * Transforms the given context for the given item into a default one. Like
	 * that we ensure the same context across different views for the same entity.
	 */
	private function transformContext(string $context, mixed $item): string
	{
		// Use help from fields as we have the same issue there
		$parts = FieldsHelper::extract($context, $item) ?: [];
		if (\count($parts) === 2) {
			$context = implode('.', $parts);
		}

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
