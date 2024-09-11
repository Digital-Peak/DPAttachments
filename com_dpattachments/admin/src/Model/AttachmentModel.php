<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2013 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

namespace DigitalPeak\Component\DPAttachments\Administrator\Model;

use DigitalPeak\Component\DPAttachments\Administrator\Extension\DPAttachmentsComponent;
use DigitalPeak\Component\DPAttachments\Administrator\Table\AttachmentTable;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\User\UserFactoryAwareInterface;
use Joomla\CMS\User\UserFactoryAwareTrait;
use Joomla\Filesystem\File;
use Joomla\Utilities\ArrayHelper;

class AttachmentModel extends AdminModel implements UserFactoryAwareInterface
{
	use UserFactoryAwareTrait;

	protected $text_prefix = 'COM_DPATTACHMENTS';

	/**
	 * @param AttachmentTable $record
	 */
	protected function canDelete($record)
	{
		if (!empty($record->id)) {
			if ($record->state != -2) {
				return false;
			}

			return $this->bootComponent('dpattachments')->canDo('core.delete', $record->context, $record->item_id);
		}

		return parent::canDelete($record);
	}

	/**
	 * @param AttachmentTable $record
	 */
	protected function canEditState($record)
	{
		if (!empty($record->id)) {
			return $this->bootComponent('dpattachments')->canDo('core.edit.state', $record->context, $record->item_id);
		}

		return parent::canEditState($record);
	}

	/**
	 * @param AttachmentTable $table
	 */
	protected function prepareTable($table)
	{
		// Increment the content version number.
		$table->version++;
	}

	public function upload(array $data): bool
	{
		if (!$this->bootComponent('dpattachments')->canDo('core.edit', $data['context'], $data['item_id'])) {
			throw new \Exception(Text::_('COM_DPATTACHMENTS_UPLOAD_NO_PERMISSION'));
		}

		$fileName = $_FILES['file']['name'];

		if ($fileName == 'blob') {
			$extension = explode('/', (string)$_FILES['file']['type']);
			if (\count($extension) > 1) {
				$fileName = 'clipboard.' . $extension[1];
			}
		}

		$uploadedFileNameParts = explode('.', (string)$fileName);
		$uploadedFileExtension = array_pop($uploadedFileNameParts);

		$validFileExts = explode(
			',',
			(string)ComponentHelper::getParams('com_dpattachments')->get('attachment_extensions', 'gif,jpg,jpeg,png,zip,rar,csv,txt,pdf')
		);

		$extOk = false;

		foreach ($validFileExts as $value) {
			if (preg_match(\sprintf('/%s/i', $value), $uploadedFileExtension)) {
				$extOk = true;
			}
		}

		if ($extOk == false) {
			throw new \Exception(Text::sprintf('COM_DPATTACHMENTS_UPLOAD_INVALID_EXTENSION', implode(',', $validFileExts)));
		}

		$fileName = preg_replace("/[^\p{L}|0-9]+/u", "-", substr((string)$fileName, 0, \strlen((string)$fileName) - \strlen($uploadedFileExtension) - 1)) . '.' .
			$uploadedFileExtension;

		$targetFile = $this->bootComponent('dpattachments')->getPath($fileName, $data['context']);
		if (file_exists($targetFile)) {
			$fileName   = Factory::getDate()->format('YmdHis') . '-' . $fileName;
			$targetFile = $this->bootComponent('dpattachments')->getPath($fileName, $data['context']);
		}

		$descriptor = ['tmp_name' => $_FILES['file']['tmp_name'], 'name' => basename((string)$targetFile)];
		if (!ComponentHelper::getParams('com_dpattachments')->get('allow_unsafe_uploads', 0) && !InputFilter::isSafeFile($descriptor)) {
			throw new \Exception(Text::_('COM_DPATTACHMENTS_UPLOAD_ERROR'));
		}

		if (!File::upload($_FILES['file']['tmp_name'], $targetFile)) {
			throw new \Exception(Text::_('COM_DPATTACHMENTS_UPLOAD_ERROR'));
		}

		$data['path'] = $fileName;
		$data['size'] = $_FILES['file']['size'];

		$result = parent::save($data);
		if (!$result) {
			throw new \Exception(Text::_('COM_DPATTACHMENTS_UPLOAD_ERROR'));
		}

		return $result;
	}

	public function getTable($type = 'Attachment', $prefix = 'DPAttachmentsTable', $config = [])
	{
		return parent::getTable($type, $prefix, $config);
	}

	public function getForm($data = [], $loadData = true)
	{
		PluginHelper::importPlugin('dpattachments');

		// Get the form.
		Form::addFormPath(JPATH_ADMINISTRATOR . '/components/com_dpattachments/forms');
		$form = $this->loadForm('com_dpattachments.attachment', 'attachment', ['control' => 'jform', 'load_data' => $loadData]);

		$user = $this->getCurrentUser();

		// Check for existing dpattachment.
		// Modify the form based on Edit State access controls.
		if (!$user->authorise('core.edit.state', 'com_dpattachments')) {
			// Disable fields for display.
			$form->setFieldAttribute('publish_up', 'disabled', 'true');
			$form->setFieldAttribute('publish_down', 'disabled', 'true');
			$form->setFieldAttribute('state', 'disabled', 'true');

			// Disable fields while saving.
			// The controller has already verified this is an dpattachment you
			$form->setFieldAttribute('publish_up', 'filter', 'unset');
			$form->setFieldAttribute('publish_down', 'filter', 'unset');
			$form->setFieldAttribute('state', 'filter', 'unset');
		}

		return $form;
	}

	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$app = Factory::getApplication();
		if (!$app instanceof CMSApplication) {
			return [];
		}

		$data = $app->getUserState('com_dpattachments.edit.attachment.data', []);

		if (empty($data)) {
			$data = $this->getItem();

			// Prime some default values.
			if (\is_object($data) && $this->getState('attachment.id') == 0) {
				// @phpstan-ignore-next-line
				$data->set('itemid', $app->input->getInt('itemid', $app->getUserState('com_dpattachments.item.filter.itemid', 0)));
			}
		}

		$this->preprocessData('com_dpattachments.item', $data);

		return $data;
	}

	public function hit(int $pk = 0): bool
	{
		$app = Factory::getApplication();
		if (!$app instanceof CMSApplication) {
			return true;
		}

		if ($app->input->getInt('hitcount', 1) === 0) {
			return true;
		}

		$pk = $pk === 0 ? $this->getState('case.id') : $pk;

		$this->getDatabase()->setQuery('UPDATE #__dpattachments SET hits = hits + 1 WHERE id = ' . (int)$pk)->execute();

		return true;
	}

	public function delete(&$pks)
	{
		$attachments = [];
		foreach (ArrayHelper::toInteger((array)$pks) as $id) {
			$attachment = $this->getItem($id);
			if (!\is_object($attachment)) {
				continue;
			}

			$attachments[] = $attachment;
		}

		$success = parent::delete($pks);
		if (!$success) {
			return $success;
		}

		/** @var DPAttachmentsComponent $component */
		$component = $this->bootComponent('dpattachments');

		foreach ($attachments as $attachment) {
			unlink($component->getPath($attachment->path, $attachment->context));
		}

		return $success;
	}

	public function getAuthor(int $userId): array
	{
		$userInfo                 = [];
		$user                     = $this->getUserFactory()->loadUserById($userId);
		$userInfo['author_id']    = $user->id;
		$userInfo['author_name']  = $user->name;
		$userInfo['author_email'] = $user->email;

		return $userInfo;
	}
}
