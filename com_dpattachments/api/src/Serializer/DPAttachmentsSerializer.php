<?php
/**
 * @package   DPAttachments
 * @copyright Copyright (C) 2026 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

namespace DigitalPeak\Component\DPAttachments\Api\Serializer;

\defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;
use Joomla\CMS\Serializer\JoomlaSerializer;
use Joomla\CMS\Uri\Uri;
use Tobscure\JsonApi\Relationship;
use Tobscure\JsonApi\Resource;

class DPAttachmentsSerializer extends JoomlaSerializer
{
	/**
	 * @param \stdClass $item
	 */
	public function createdBy($item): Relationship
	{
		return $this->getUserRelationship((int)$item->created_by);
	}

	/**
	 * @param \stdClass $item
	 */
	public function modifiedBy($item): Relationship
	{
		return $this->getUserRelationship((int)$item->modified_by);
	}

	private function getUserRelationship(int $userId): Relationship
	{
		$resource = (new Resource($userId, new JoomlaSerializer('users')))
			->addLink('self', Route::link('site', Uri::root() . 'api/index.php/v1/users/' . $userId));

		return new Relationship($resource);
	}
}
