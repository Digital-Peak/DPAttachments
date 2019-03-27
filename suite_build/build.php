<?php

/**
 * @package    DPAttachments
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2012 - 2019 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

/**
 * Build release files for the DPAttachments package.
 */
class DPAttachmentsReleaseBuild
{

	public function build()
	{
		$root      = dirname(dirname(__FILE__));
		$buildDir  = dirname(__FILE__);
		$dpVersion = new SimpleXMLElement(file_get_contents(dirname(__FILE__) . '/pkg_dpattachments.xml'));
		$dpVersion = (string)$dpVersion->version;

		echo ' Creating version: ' . $dpVersion;

		$dpVersion = str_replace('.', '_', $dpVersion);

		exec('rm -rf ' . $buildDir . '/dist');
		exec('rm -rf ' . $buildDir . '/build');

		mkdir($buildDir . '/dist');
		mkdir($buildDir . '/build');
		$dpDir = $buildDir . '/build/DPAttachments';
		mkdir($dpDir);

		// Component
		$this->createZip($buildDir . '/../com_dpattachments', $dpDir . '/com_dpattachments.zip',
			array(
				'com_dpattachments/admin/com_dpattachments.xml',
				'com_dpattachments/media/scss',
				'com_dpattachments/media/attachments'
			), array(
				'com_dpattachments/admin/dpattachments.xml' => 'com_dpattachments/dpattachments.xml'
			));

		// Plugins
		$this->createZip($buildDir . '/../plg_content_dpattachments', $dpDir . '/plg_content_dpattachments.zip');

		// Making the installable zip files
		copy($buildDir . '/license.txt', $dpDir . '/license.txt');
		copy($buildDir . '/pkg_dpattachments.xml', $dpDir . '/pkg_dpattachments.xml');

		$this->createZip($dpDir, $buildDir . '/dist/DPAttachments_Core_' . $dpVersion . '.zip');
	}

	private function createZip($folder, $zipFile, $excludes = array(), $substitutes = array())
	{
		$root = dirname(dirname(__FILE__));

		$zip = new ZipArchive();
		$zip->open($zipFile, ZIPARCHIVE::CREATE);

		$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($folder), RecursiveIteratorIterator::LEAVES_ONLY);

		foreach ($files as $name => $file) {
			// Get real path for current file
			$filePath = $file->getRealPath();
			$fileName = str_replace($root . '/', '', $filePath);
			$fileName = str_replace('suite_build/build/DPAttachments', '', $fileName);

			$ignore = false;
			foreach ($excludes as $exclude) {
				if (strpos($fileName, $exclude) !== false) {
					$ignore = true;
					break;
				}
			}

			if ($ignore || is_dir($filePath)) {
				continue;
			}
			if (key_exists($fileName, $substitutes)) {
				$fileName = $substitutes[$fileName];
			}

			$fileName = trim($fileName, '/');

			// Add current file to archive
			$zip->addFile($filePath, $fileName);
		}

		$zip->close();
	}
}

$build = new DPAttachmentsReleaseBuild();
$build->build();
