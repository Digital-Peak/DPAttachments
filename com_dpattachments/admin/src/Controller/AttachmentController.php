<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2013 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

namespace DigitalPeak\Component\DPAttachments\Administrator\Controller;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Session\Session;

class AttachmentController extends FormController
{
	protected function allowEdit($data = [], $key = 'id')
	{
		$recordId = (int)isset($data[$key]) ? $data[$key] : 0;

		$record = $this->getModel()->getItem($recordId);
		if (!empty($record)) {
			return $this->app->bootComponent('dpattachments')->canDo('core.edit', $record->context, $record->item_id);
		}

		return parent::allowEdit($data, $key);
	}

	public function upload()
	{
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$data       = $this->input->get('attachment', [], 'array');
		$data['id'] = 0;

		$model   = $this->getModel('Attachment', 'Administrator');
		$success = $model->upload($data);

		$returnData = ['html' => '', 'context' => $data['context'], 'item_id' => $data['item_id']];
		if ($success) {
			$this->app->enqueueMessage(Text::_('COM_DPATTACHMENTS_UPLOAD_SUCCESS'), 'success');

			$content = $this->app->bootComponent('dpattachments')->renderLayout(
				'attachment.render',
				['attachment' => $model->getItem($model->getState($model->getName() . '.id'))]
			);
			$returnData['html'] = '<div>' . $content . '</div>';
		} else {
			$this->app->enqueueMessage(Text::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()), 'error');
		}

		$this->sendMessage(null, !$success, $returnData);
	}

	public function download()
	{
		$attachment = $this->getModel()->getItem($this->input->get('id'));
		if (!$attachment) {
			header('HTTP/1.0 404 Not Found');
			exit(0);
		}

		$filename = $this->app->bootComponent('dpattachments')->getPath($attachment->path, $attachment->context);
		if (!file_exists($filename)) {
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
		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Cache-Control: public', false);

		// Send MIME headers
		header('Content-Description: File Transfer');
		header('Content-Type: ' . $mime_type);
		header('Accept-Ranges: bytes');
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
			list($size_unit, $range_orig) = explode('=', $_SERVER['HTTP_RANGE'], 2);

			if ($size_unit == 'bytes') {
				// Multiple ranges could be specified at the same time, but for
				// simplicity only serve the first range
				list($range, $extra_ranges) = explode(',', $range_orig, 2);
			} else {
				$range = '';
			}
		} else {
			$range = '';
		}

		if ($range) {
			// Figure out download piece from range (if set)
			list($seek_start, $seek_end) = explode('-', $range, 2);

			// Set start and end based on range (if set), else set defaults also check for invalid ranges
			$seek_end   = (empty($seek_end)) ? -1 : min(abs(intval($seek_end)), ($filesize - 1));
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

		$this->app->close();
	}

	public function publish()
	{
		Session::checkToken('get') or jexit(Text::_('JINVALID_TOKEN'));

		$id      = $this->input->get('id');
		$model   = $this->getModel();
		$success = $model->publish($id, $this->input->getInt('state'));

		if (!$success) {
			throw new \Exception(Text::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()));
		}

		$this->setMessage(Text::plural('COM_DPATTACHMENTS_N_ITEMS_TRASHED', 1), 'success');

		$this->setRedirect('index.php?option=com_dpattachments&view=attachments');
	}

	private function sendMessage($message, $error = false, array $data = [])
	{
		ob_clean();

		if (!$error) {
			$this->app->enqueueMessage($message);
			echo new JsonResponse($data);
		} else {
			$this->app->enqueueMessage($message, 'error');
			echo new JsonResponse($data, '', true);
		}

		$this->app->close();
	}
}
