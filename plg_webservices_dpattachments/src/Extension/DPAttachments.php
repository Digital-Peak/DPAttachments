<?php
/**
 * @package   DPAttachments
 * @copyright Copyright (C) 2026 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

namespace DigitalPeak\Plugin\Webservices\DPAttachments\Extension;

\defined('_JEXEC') or die();

use Joomla\CMS\Event\Application\BeforeApiRouteEvent;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;

class DPAttachments extends CMSPlugin implements SubscriberInterface
{
	public static function getSubscribedEvents(): array
	{
		return ['onBeforeApiRoute' => 'onBeforeApiRoute'];
	}

	public function onBeforeApiRoute(BeforeApiRouteEvent $event): void
	{
		$router = $event->getRouter();

		$router->createCRUDRoutes(
			'v1/dpattachments/attachments',
			'attachments',
			['component' => 'com_dpattachments']
		);

		$router->createCRUDRoutes(
			'v1/fields/dpattachments/attachments',
			'fields',
			['component' => 'com_fields', 'context' => 'com_dpattachments.attachment']
		);

		$router->createCRUDRoutes(
			'v1/fields/groups/dpattachments/attachments',
			'groups',
			['component' => 'com_fields', 'context' => 'com_dpattachments.attachment']
		);
	}
}
