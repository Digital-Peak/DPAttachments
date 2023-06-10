<?php
/**
 * @package   DPAttachments
 * @copyright Copyright (C) 2023 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

$files = [
// From v1.0.0 to v4.0.0
'/administrator/components/com_dpattachments/controller.php',
'/administrator/components/com_dpattachments/controllers',
'/administrator/components/com_dpattachments/dpattachments.php',
'/administrator/components/com_dpattachments/helpers',
'/administrator/components/com_dpattachments/index.html',
'/administrator/components/com_dpattachments/libraries',
'/administrator/components/com_dpattachments/models',
'/administrator/components/com_dpattachments/sql/index.html',
'/administrator/components/com_dpattachments/tables',
'/administrator/components/com_dpattachments/views',
'/components/com_dpattachments/controller.php',
'/components/com_dpattachments/controllers',
'/components/com_dpattachments/dpattachments.php',
'/components/com_dpattachments/index.html',
'/components/com_dpattachments/libraries',
'/components/com_dpattachments/models',
'/components/com_dpattachments/views',
'/plugins/system/dpattachments',

// From v4.0.4 to v5.0.0
'/media/com_dpattachments/css/layouts',
'/media/com_dpattachments/css/tingle',
'/media/com_dpattachments/css/views',
'/media/com_dpattachments/js/layouts',
'/media/com_dpattachments/js/tingle',
'/media/com_dpattachments/js/views',
'/plugins/content/dpattachments/src/DPAttachments.php',

// From v5.0.0 to v5.1.0
'/plugins/content/dpattachments/forms/context',

// From v5.2.0 to case 10008
'/media/com_dpattachments/images/icons/ban-solid.svg',
'/media/com_dpattachments/images/icons/check-solid.svg',
'/media/com_dpattachments/images/icons/download-solid.svg',
'/media/com_dpattachments/images/icons/pencil-alt-solid.svg',
'/media/com_dpattachments/images/icons/trash-alt-solid.svg',
'/media/com_dpattachments/images/icons/upload-solid.svg',
];

foreach ($files as $file) {
	$fullPath = JPATH_ROOT . $file;

	if (empty($file) || !file_exists($fullPath)) {
		continue;
	}

	if (pathinfo($fullPath, PATHINFO_EXTENSION)) {
		unlink($fullPath);
		continue;
	}

	try {
		$files = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($fullPath, RecursiveDirectoryIterator::SKIP_DOTS),
			RecursiveIteratorIterator::CHILD_FIRST
		);

		foreach ($files as $fileinfo) {
			$todo = $fileinfo->isDir() ? 'rmdir' : 'unlink';
			$todo($fileinfo->getRealPath());
		}

		rmdir($fullPath);
	} catch (Exception $e) {
	}
}
