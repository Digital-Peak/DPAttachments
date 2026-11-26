<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2026 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

namespace DigitalPeak\Component\DPAttachments\Administrator\Event;

use Joomla\CMS\Event\AbstractEvent;

/**
 * Event onDPAttachmentsCheckPermission, dispatched when a permission is checked.
 * When a listener grants the permission, no further checks are done.
 */
class CheckPermissionEvent extends AbstractEvent
{
	public function __construct(string $name, array $arguments = [])
	{
		foreach (['action', 'context', 'item_id'] as $argument) {
			if (!\array_key_exists($argument, $arguments)) {
				throw new \BadMethodCallException(\sprintf("Argument '%s' of event %s is required but has not been provided", $argument, $name));
			}
		}

		$arguments['item_id'] = (string)$arguments['item_id'];

		parent::__construct($name, $arguments);
	}

	protected function onSetAction(string $value): string
	{
		return $value;
	}

	protected function onSetContext(string $value): string
	{
		return $value;
	}

	protected function onSetAllowed(bool $value): bool
	{
		return $value;
	}

	public function getAction(): string
	{
		return $this->arguments['action'];
	}

	public function getContext(): string
	{
		return $this->arguments['context'];
	}

	public function getItemId(): string
	{
		return $this->arguments['item_id'];
	}

	/**
	 * Grant the permission, no further checks are done.
	 */
	public function allow(): void
	{
		$this->setArgument('allowed', true);
	}

	/**
	 * Returns if a listener has granted the permission.
	 */
	public function isAllowed(): bool
	{
		return ($this->arguments['allowed'] ?? false) === true;
	}
}
