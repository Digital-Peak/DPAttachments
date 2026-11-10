<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2026 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

namespace DigitalPeak\Component\DPAttachments\Administrator\Event;

/**
 * Event onDPAttachmentsAfterRenderLayout, dispatched after a layout is rendered.
 */
class AfterRenderLayoutEvent extends AbstractRenderLayoutEvent
{
	public function __construct(string $name, array $arguments = [])
	{
		if (!\array_key_exists('content', $arguments)) {
			throw new \BadMethodCallException(\sprintf("Argument 'content' of event %s is required but has not been provided", $name));
		}

		parent::__construct($name, $arguments);
	}

	protected function onSetContent(string $value): string
	{
		return $value;
	}

	/**
	 * The rendered content of the layout.
	 */
	public function getContent(): string
	{
		return $this->arguments['content'];
	}

	/**
	 * Replace the rendered content, it is the output of the layout.
	 */
	public function updateContent(string $content): void
	{
		$this->setArgument('content', $content);
	}
}
