<?php
/**
 * @package    DPAttachments
 * @copyright  (C) 2013 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class DPAttachmentsControllerAttachment extends JControllerForm
{

	protected $view_item = 'form';

	protected function allowEdit($data = [], $key = 'id')
	{
		$recordId = (int)isset($data[$key]) ? $data[$key] : 0;
		$user     = JFactory::getUser();

		$record = $this->getModel()->getItem($recordId);
		if (!empty($record)) {
			return \DPAttachments\Helper\Core::canDo('core.edit', $record->context, $record->item_id);
		}

		return parent::allowEdit($data, $key);
	}

	public function cancel($key = 'a_id')
	{
		parent::cancel($key);

		$this->setRedirect($this->getReturnPage());
	}

	public function edit($key = null, $urlVar = 'a_id')
	{
		return parent::edit($key, $urlVar);
	}

	public function getModel($name = 'form', $prefix = '', $config = ['ignore_request' => true])
	{
		return parent::getModel($name, $prefix, $config);
	}

	protected function getRedirectToItemAppend($recordId = null, $urlVar = 'a_id')
	{
		$tmpl   = $this->input->get('tmpl');
		$append = '';

		if ($tmpl) {
			$append .= '&tmpl=' . $tmpl;
		}

		$append .= '&layout=edit';

		if ($recordId) {
			$append .= '&' . $urlVar . '=' . $recordId;
		}

		$itemId = $this->input->getInt('Itemid');
		$return = $this->getReturnPage();

		if ($itemId) {
			$append .= '&Itemid=' . $itemId;
		}

		if ($return) {
			$append .= '&return=' . base64_encode($return);
		}

		return $append;
	}

	protected function getReturnPage()
	{
		$return = $this->input->get('return', null, 'base64');

		if (empty($return) || !JUri::isInternal(base64_decode($return))) {
			return JUri::base();
		} else {
			return base64_decode($return);
		}
	}

	protected function postSaveHook(JModelLegacy $model, $validData = [])
	{
		return;
	}

	public function save($key = null, $urlVar = 'a_id')
	{
		$result = parent::save($key, $urlVar);

		// If ok, redirect to the return page.
		if ($result) {
			$this->setRedirect($this->getReturnPage());
		}

		return $result;
	}

	public function upload()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		JFactory::getLanguage()->load('com_dpattachments', JPATH_ADMINISTRATOR . '/components/com_dpattachments');

		$data       = $this->input->get('attachment', [], 'array');
		$data['id'] = 0;

		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_dpattachments/models', 'DPAttachmentsModel');
		$model   = $this->getModel('Attachment');
		$success = $model->upload($data);

		$returnData = ['html' => '', 'context' => $data['context'], 'item_id' => $data['item_id']];
		if ($success) {
			JFactory::getApplication()->enqueueMessage(JText::_('COM_DPATTACHMENTS_UPLOAD_SUCCESS'), 'success');

			$content            = \DPAttachments\Helper\Core::renderLayout(
				'attachment.render',
				['attachment' => $model->getItem($model->getState($model->getName() . '.id'))]
			);
			$returnData['html'] = '<div>' . $content . '</div>';
		} else {
			JFactory::getApplication()->enqueueMessage(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()), 'error');
		}

		\DPAttachments\Helper\DPAttachmentsHelper::sendMessage(null, !$success, $returnData);
	}

	public function download()
	{
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_dpattachments/models', 'DPAttachmentsModel');
		$attachment = $this->getModel()->getItem($this->input->get('id'));
		if (!$attachment) {
			header('HTTP/1.0 404 Not Found');
			exit(0);
		}

		JLoader::import('joomla.filesystem.folder');
		JLoader::import('joomla.filesystem.file');

		$filename = \DPAttachments\Helper\Core::getPath($attachment->path, $attachment->context);
		if (!JFile::exists($filename)) {
			header('HTTP/1.0 404 Not Found');
			exit(0);
		}

		$this->getModel()->hit($attachment->id);

		$basename  = @basename($filename);
		$filesize  = @filesize($filename);
		$mime_type = 'application/octet-stream';

		// Clear cache
		while (@ob_end_clean()) {
			;
		}

		// Fix IE bugs
		if (isset($_SERVER['HTTP_USER_AGENT']) && strstr($_SERVER['HTTP_USER_AGENT'], 'MSIE')) {
			$header_file = preg_replace('/\./', '%2e', $basename, substr_count($basename, '.') - 1);

			if (ini_get('zlib.output_compression')) {
				ini_set('zlib.output_compression', 'Off');
			}
		} else {
			$header_file = $basename;
		}

		@clearstatcache();

		// Disable caching
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: public", false);

		// Send MIME headers
		header("Content-Description: File Transfer");
		header('Content-Type: ' . $mime_type);
		header("Accept-Ranges: bytes");
		header('Content-Disposition: attachment; filename="' . $header_file . '"');
		header('Content-Transfer-Encoding: binary');
		header('Connection: close');

		error_reporting(0);
		if (!ini_get('safe_mode')) {
			set_time_limit(0);
		}

		// Support resumable downloads
		$isResumable = false;
		$seek_start  = 0;
		$seek_end    = $filesize - 1;
		if (isset($_SERVER['HTTP_RANGE'])) {
			list ($size_unit, $range_orig) = explode('=', $_SERVER['HTTP_RANGE'], 2);

			if ($size_unit == 'bytes') {
				// Multiple ranges could be specified at the same time, but for
				// simplicity only serve the first range
				list ($range, $extra_ranges) = explode(',', $range_orig, 2);
			} else {
				$range = '';
			}
		} else {
			$range = '';
		}

		if ($range) {
			// Figure out download piece from range (if set)
			list ($seek_start, $seek_end) = explode('-', $range, 2);

			// Set start and end based on range (if set), else set defaults
			// also check for invalid ranges.
			$seek_end   = (empty($seek_end)) ? ($size - 1) : min(abs(intval($seek_end)), ($filesize - 1));
			$seek_start = (empty($seek_start) || $seek_end < abs(intval($seek_start))) ? 0 : max(abs(intval($seek_start)), 0);

			$isResumable = true;
		}

		// Use 1M chunks for echoing the data to the browser
		$chunksize = 1024 * 1024;
		$buffer    = '';
		$handle    = @fopen($filename, 'rb');
		if ($handle !== false) {
			if ($isResumable) {
				// Only send partial content header if downloading a piece of
				// the file (IE workaround)
				if ($seek_start > 0 || $seek_end < ($filesize - 1)) {
					header('HTTP/1.1 206 Partial Content');
				}

				// Necessary headers
				$totalLength = $seek_end - $seek_start + 1;
				header('Content-Range: bytes ' . $seek_start . '-' . $seek_end . '/' . $size);
				header('Content-Length: ' . $totalLength);

				// Seek to start
				fseek($handle, $seek_start);
			} else {
				$isResumable = false;

				// Notify of filesize, if this info is available
				if ($filesize > 0) {
					header('Content-Length: ' . (int)$filesize);
				}
			}
			$read = 0;
			while (!feof($handle) && ($chunksize > 0)) {
				if ($isResumable) {
					if ($totalLength - $read < $chunksize) {
						$chunksize = $totalLength - $read;
						if ($chunksize < 0) {
							continue;
						}
					}
				}
				$buffer = fread($handle, $chunksize);
				if ($isResumable) {
					$read += strlen($buffer);
				}
				echo $buffer;
				@ob_flush();
				flush();
			}
			@fclose($handle);
		} else {
			// Notify of filesize, if this info is available
			if ($filesize > 0) {
				header('Content-Length: ' . (int)$filesize);
			}
			@readfile($filename);
		}

		JFactory::getApplication()->close();
	}

	public function publish()
	{
		JSession::checkToken('get') or jexit(JText::_('JINVALID_TOKEN'));

		$id      = $this->input->get('id');
		$model   = $this->getModel();
		$success = $model->publish($id, $this->input->getInt('state'));

		if ($success) {
			$message = 'COM_DPATTACHMENTS_N_ITEMS_TRASHED';
			$this->setMessage(JText::plural($message, 1), 'success');
		} else {
			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()));
			$this->setMessage($this->getError(), 'error');
		}

		$this->setRedirect($this->getReturnPage($this->input->getInt('id')));
	}
}
