<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2013 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace DigitalPeak\Component\DPAttachments\Administrator\Model;

defined('_JEXEC') or die();

use DigitalPeak\Component\DPAttachments\Administrator\Extension\DPAttachmentsComponent;
use Exception;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\Utilities\ArrayHelper;

class AttachmentModel extends AdminModel
{
    protected $text_prefix = 'COM_DPATTACHMENTS';

    protected function canDelete($record)
    {
        if (!empty($record->id)) {
            if ($record->state != -2) {
                return false;
            }

            return Factory::getApplication()->bootComponent('dpattachments')->canDo('core.delete', $record->context, $record->item_id);
        }

        return parent::canDelete($record);
    }

    protected function canEditState($record)
    {
        if (!empty($record->id)) {
            return Factory::getApplication()->bootComponent('dpattachments')->canDo('core.edit.state', $record->context, $record->item_id);
        }

        return parent::canEditState($record);
    }

    protected function prepareTable($table)
    {
        // Set the publish date to now
        $db = $this->getDbo();
        if ($table->state == 1 && (int)$table->publish_up == 0) {
            $table->publish_up = Factory::getDate()->toSql();
        }

        if ($table->state == 1 && intval($table->publish_down) == 0) {
            $table->publish_down = $db->getNullDate();
        }

        // Increment the content version number.
        $table->version++;
    }

    public function upload($data)
    {
        if (!Factory::getApplication()->bootComponent('dpattachments')->canDo('core.edit', $data['context'], $data['item_id'])) {
            throw new Exception(Text::_('COM_DPATTACHMENTS_UPLOAD_NO_PERMISSION'));
        }

        $fileName = $_FILES['file']['name'];

        if ($fileName == 'blob') {
            $extension = explode('/', $_FILES['file']['type']);
            if (count($extension) > 1) {
                $fileName = 'clipboard.' . $extension[1];
            }
        }

        $uploadedFileNameParts = explode('.', $fileName);
        $uploadedFileExtension = array_pop($uploadedFileNameParts);

        $validFileExts = explode(
            ',',
            ComponentHelper::getParams('com_dpattachments')->get('attachment_extensions', 'gif,jpg,jpeg,png,zip,rar,csv,txt,pdf')
        );

        $extOk = false;

        foreach ($validFileExts as $key => $value) {
            if (preg_match("/$value/i", $uploadedFileExtension)) {
                $extOk = true;
            }
        }

        if ($extOk == false) {
            throw new Exception(Text::sprintf('COM_DPATTACHMENTS_UPLOAD_INVALID_EXTENSION', implode(',', $validFileExts)));
        }

        $fileName = preg_replace("/[^\p{L}|0-9]+/u", "-", substr($fileName, 0, strlen($fileName) - strlen($uploadedFileExtension) - 1)) . '.' .
            $uploadedFileExtension;

        $targetFile = Factory::getApplication()->bootComponent('dpattachments')->getPath($fileName, $data['context']);
        if (file_exists($targetFile)) {
            $fileName   = Factory::getDate()->format('YmdHi') . '-' . $fileName;
            $targetFile = Factory::getApplication()->bootComponent('dpattachments')->getPath($fileName, $data['context']);
        }

        if (!File::upload(
            $_FILES['file']['tmp_name'],
            $targetFile,
            false,
            ComponentHelper::getParams('com_dpattachments')->get('allow_unsafe_uploads', 0)
        )) {
            throw new Exception(Text::_('COM_DPATTACHMENTS_UPLOAD_ERROR'));
        }

        $data['path'] = $fileName;
        $data['size'] = $_FILES['file']['size'];

        return parent::save($data);
    }

    public function getTable($type = 'Attachment', $prefix = 'DPAttachmentsTable', $config = [])
    {
        return parent::getTable($type, $prefix, $config);
    }

    public function getForm($data = [], $loadData = true)
    {
        // Get the form.
        Form::addFormPath(JPATH_ADMINISTRATOR . '/components/com_dpattachments/forms');
        $form = $this->loadForm('com_dpattachments.attachment', 'attachment', ['control' => 'jform', 'load_data' => $loadData]);
        if (empty($form)) {
            return false;
        }

        $user = Factory::getUser();

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
        $app  = Factory::getApplication();
        $data = $app->getUserState('com_dpattachments.edit.attachment.data', []);

        if (empty($data)) {
            $data = $this->getItem();

            // Prime some default values.
            if ($this->getState('attachment.id') == 0) {
                $data->set('itemid', $app->input->getInt('itemid', $app->getUserState('com_dpattachments.item.filter.itemid')));
            }
        }

        $this->preprocessData('com_dpattachments.item', $data);

        return $data;
    }

    public function hit($pk = 0)
    {
        $input    = Factory::getApplication()->input;
        $hitcount = $input->getInt('hitcount', 1);

        if ($hitcount) {
            $pk = (!empty($pk)) ? $pk : (int)$this->getState('case.id');
            $db = $this->getDbo();

            $db->setQuery('UPDATE #__dpattachments' . ' SET hits = hits + 1' . ' WHERE id = ' . (int)$pk);

            $db->execute();
        }

        return true;
    }

    public function delete(&$pks)
    {
        $attachments = [];
        foreach (ArrayHelper::toInteger((array) $pks) as  $id) {
            $attachments[] = $this->getItem($id);
        }

        $success = parent::delete($pks);
        if (!$success) {
            return $success;
        }

        /** @var DPAttachmentsComponent $component */
        $component = Factory::getApplication()->bootComponent('dpattachments');

        foreach ($attachments as $attachment) {
            unlink($component->getPath($attachment->path, $attachment->context));
        }

        return $success;
    }
}
