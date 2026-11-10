<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2026 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

namespace DigitalPeak\Component\DPAttachments\Administrator\Event;

/**
 * Event onDPAttachmentsBeforeRenderLayout, dispatched before a layout is rendered.
 */
class BeforeRenderLayoutEvent extends AbstractRenderLayoutEvent
{
	/**
	 * Replace the display data, it is used to render the layout.
	 */
	public function updateData(array $data): void
	{
		$this->setArgument('data', $data);
	}
}
