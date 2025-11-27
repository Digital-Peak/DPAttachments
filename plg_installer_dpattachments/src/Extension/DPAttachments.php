<?php
/**
 * @package   DPAttachments
 * @copyright Copyright (C) 2025 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

namespace DigitalPeak\Plugin\Installer\DPAttachments\Extension;

\defined('_JEXEC') or die();

use Joomla\CMS\Event\Installer\BeforeUpdateSiteDownloadEvent;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseAwareTrait;

class DPAttachments extends CMSPlugin
{
	use DatabaseAwareTrait;

	public function onInstallerBeforeUpdateSiteDownload(BeforeUpdateSiteDownloadEvent $event): void
	{
		$url = $event->getUrl();
		if (!str_contains($url, 'digital-peak.com')) {
			return;
		}

		$query = $this->getDatabase()->getQuery(true);
		$query->select('name')->from('#__update_sites');
		$query->where('location = :location')->bind(':location', $url);

		$this->getDatabase()->setQuery($query);
		if (!str_contains((string)$this->getDatabase()->loadResult(), 'DPAttachments')) {
			return;
		}

		$uri = Uri::getInstance($url);

		// Set versions, so we get a compact update XML back with only versions greater than whats installed
		// to prevent timeouts and other network issues
		$uri->setVar('j', JVERSION);
		$uri->setVar('p', phpversion());
		$uri->setVar('m', $this->getDatabase()->getVersion());

		$path = JPATH_ADMINISTRATOR . '/components/com_dpattachments/dpattachments.xml';
		if (file_exists($path)) {
			$manifest = simplexml_load_file($path);
			$uri->setVar('v', $manifest instanceof \SimpleXMLElement ? (string)$manifest->version : '');
		}

		if ($uri->getVar('v') === 'DP_DEPLOY_VERSION') {
			return;
		}

		$event->updateUrl($uri->toString());
	}
}
