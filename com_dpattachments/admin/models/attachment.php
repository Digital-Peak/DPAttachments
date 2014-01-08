<?php
/**
 * @package    DPAttachments
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2012 - 2014 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

JLoader::import('helpers.dpattachments', JPATH_COMPONENT_ADMINISTRATOR);

JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_dpattachments/tables');

class DPAttachmentsModelAttachment extends JModelAdmin
{

	protected $text_prefix = 'COM_DPATTACHMENTS';

	protected function canDelete ($record)
	{
		if (! empty($record->id))
		{
			if ($record->state != - 2)
			{
				return false;
			}
			return DPAttachmentsCore::canDo('core.delete', $record->context, $record->item_id);
		}
		return parent::canDelete($record);
	}

	protected function canEditState ($record)
	{
		if (! empty($record->id))
		{
			return DPAttachmentsCore::canDo('core.edit.state', $record->context, $record->item_id);
		}
		return parent::canEditState($record);
	}

	protected function prepareTable ($table)
	{
		// Set the publish date to now
		$db = $this->getDbo();
		if ($table->state == 1 && (int) $table->publish_up == 0)
		{
			$table->publish_up = JFactory::getDate()->toSql();
		}

		if ($table->state == 1 && intval($table->publish_down) == 0)
		{
			$table->publish_down = $db->getNullDate();
		}

		// Increment the content version number.
		$table->version ++;
	}

	public function upload ($data)
	{
		$user = JFactory::getUser();

		if (! DPAttachmentsCore::canDo('core.edit', $data['context'], $data['item_id']))
		{
			$this->setError(JText::_('COM_DPATTACHMENTS_UPLOAD_NO_PERMISSION'));
			return false;
		}

		$fileName = $_FILES['file']['name'];

		if ($fileName == 'blob')
		{
			$extension = explode('/', $_FILES['file']['type']);
			if (count($extension) > 1)
			{
				$fileName = 'clipboard.' . $extension[1];
			}
		}

		$uploadedFileNameParts = explode('.', $fileName);
		$uploadedFileExtension = array_pop($uploadedFileNameParts);

		$validFileExts = explode(',',
				JComponentHelper::getParams('com_dpattachments')->get('attachment_extensions', 'gif,jpg,jpeg,png,zip,rar,csv,txt,pdf'));

		$extOk = false;

		foreach ($validFileExts as $key => $value)
		{
			if (preg_match("/$value/i", $uploadedFileExtension))
			{
				$extOk = true;
			}
		}

		if ($extOk == false)
		{
			$this->setError(JText::sprintf('COM_DPATTACHMENTS_UPLOAD_INVALID_EXTENSION', implode(',', $validFileExts)));
			return false;
		}

		$fileName = preg_replace("/[^\p{L}]+/u", "-", substr($fileName, 0, strlen($fileName) - strlen($uploadedFileExtension) - 1)) . '.' .
				 $uploadedFileExtension;

		$targetFile = DPAttachmentsCore::getPath($fileName, $data['context']);
		JLoader::import('joomla.filesystem.file');
		if (JFile::exists($targetFile))
		{
			$fileName = JFactory::getDate()->format('YmdHi') . '-' . $fileName;
			$targetFile = DPAttachmentsCore::getPath($fileName, $data['context']);
		}
		if (! JFile::upload($_FILES['file']['tmp_name'], $targetFile))
		{
			$this->setError(JText::_('COM_DPATTACHMENTS_UPLOAD_ERROR'));
			return false;
		}

		$data['path'] = $fileName;
		$data['size'] = $_FILES['file']['size'];

		return parent::save($data);
	}

	public function getTable ($type = 'Attachment', $prefix = 'DPAttachmentsTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	public function getForm ($data = array(), $loadData = true)
	{
		// Get the form.
		JForm::addFormPath(JPATH_COMPONENT_ADMINISTRATOR . '/models/forms');
		JForm::addFieldPath(JPATH_COMPONENT_ADMINISTRATOR . '/models/fields');
		$form = $this->loadForm('com_dpattachments.attachment', 'attachment', array(
				'control' => 'jform',
				'load_data' => $loadData
		));
		if (empty($form))
		{
			return false;
		}
		$jinput = JFactory::getApplication()->input;

		// The front end calls this model and uses a_id to avoid id clashes so
		// we need to check for that first.
		if ($jinput->get('a_id'))
		{
			$id = $jinput->get('a_id', 0);
		}
		else
		{
			$id = $jinput->get('id', 0);
		}
		// Determine correct permissions to check.
		if ($this->getState('attachment.id'))
		{
			$id = $this->getState('attachment.id');
		}

		$user = JFactory::getUser();

		// Check for existing dpattachment.
		// Modify the form based on Edit State access controls.
		if (! $user->authorise('core.edit.state', 'com_dpattachments'))
		{
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

	protected function loadFormData ()
	{
		// Check the session for previously entered form data.
		$app = JFactory::getApplication();
		$data = $app->getUserState('com_dpattachments.edit.attachment.data', array());

		if (empty($data))
		{
			$data = $this->getItem();

			// Prime some default values.
			if ($this->getState('attachment.id') == 0)
			{
				$data->set('itemid', $app->input->getInt('itemid', $app->getUserState('com_dpattachments.item.filter.itemid')));
			}
		}

		$this->preprocessData('com_dpattachments.item', $data);

		return $data;
	}

	public function hit ($pk = 0)
	{
		$input = JFactory::getApplication()->input;
		$hitcount = $input->getInt('hitcount', 1);

		if ($hitcount)
		{
			$pk = (! empty($pk)) ? $pk : (int) $this->getState('case.id');
			$db = $this->getDbo();

			$db->setQuery(

			'UPDATE #__dpattachments' . ' SET hits = hits + 1' . ' WHERE id = ' . (int) $pk);

			try
			{
				$db->execute();
			}
			catch (RuntimeException $e)
			{
				$this->setError($e->getMessage());
				return false;
			}
		}
		return true;
	}
}
