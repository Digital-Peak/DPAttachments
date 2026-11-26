<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2026 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

namespace DigitalPeak\Component\DPAttachments\Administrator\Event;

use DigitalPeak\Component\DPAttachments\Administrator\Extension\DPAttachmentsComponent;
use Joomla\CMS\Event\AbstractEvent;

/**
 * Base event for the render layout events of DPAttachments.
 */
abstract class AbstractRenderLayoutEvent extends AbstractEvent
{
	public function __construct(string $name, array $arguments = [])
	{
		foreach (['name', 'data', 'component'] as $argument) {
			if (!\array_key_exists($argument, $arguments)) {
				throw new \BadMethodCallException(\sprintf("Argument '%s' of event %s is required but has not been provided", $argument, $name));
			}
		}

		parent::__construct($name, $arguments);
	}

	protected function onSetName(string $value): string
	{
		return $value;
	}

	protected function onSetData(array $value): array
	{
		return $value;
	}

	protected function onSetComponent(DPAttachmentsComponent $value): DPAttachmentsComponent
	{
		return $value;
	}

	/**
	 * The name of the layout.
	 */
	public function getLayoutName(): string
	{
		return $this->arguments['name'];
	}

	/**
	 * The display data of the layout.
	 */
	public function getData(): array
	{
		return $this->arguments['data'];
	}

	public function getComponent(): DPAttachmentsComponent
	{
		return $this->arguments['component'];
	}
}
