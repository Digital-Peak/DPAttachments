<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2026 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

namespace DigitalPeak\Component\DPAttachments\Administrator\Event;

use DigitalPeak\Component\DPAttachments\Administrator\Extension\DPAttachmentsComponent;
use Joomla\CMS\Event\AbstractEvent;
use Joomla\Registry\Registry;

/**
 * Base event for the process list events of DPAttachments.
 */
abstract class AbstractProcessListEvent extends AbstractEvent
{
	public function __construct(string $name, array $arguments = [])
	{
		foreach (['context', 'item_id', 'attachments', 'component', 'options'] as $argument) {
			if (!\array_key_exists($argument, $arguments)) {
				throw new \BadMethodCallException(\sprintf("Argument '%s' of event %s is required but has not been provided", $argument, $name));
			}
		}

		$arguments['item_id'] = (string)$arguments['item_id'];

		parent::__construct($name, $arguments);
	}

	protected function onSetContext(string $value): string
	{
		return $value;
	}

	protected function onSetAttachments(array $value): array
	{
		return $value;
	}

	protected function onSetComponent(DPAttachmentsComponent $value): DPAttachmentsComponent
	{
		return $value;
	}

	protected function onSetOptions(Registry $value): Registry
	{
		return $value;
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
	 * The attachments of the list.
	 */
	public function getAttachments(): array
	{
		return $this->arguments['attachments'];
	}

	/**
	 * Replace the attachments of the list, they are used for further processing.
	 */
	public function updateAttachments(array $attachments): void
	{
		$this->setArgument('attachments', $attachments);
	}

	public function getComponent(): DPAttachmentsComponent
	{
		return $this->arguments['component'];
	}

	public function getOptions(): Registry
	{
		return $this->arguments['options'];
	}
}
