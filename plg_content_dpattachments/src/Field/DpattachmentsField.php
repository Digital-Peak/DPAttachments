<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2022 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace DigitalPeak\Plugin\Content\DPAttachments\Field;

use DigitalPeak\Component\DPAttachments\Administrator\Extension\DPAttachmentsComponent;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;

class DpattachmentsField extends FormField
{
	protected $type = 'Dpattachments';

	protected function getInput()
	{
		$app = Factory::getApplication();

		if (!(string)$this->element['item_id']) {
			return $app->getLanguage()->_('PLG_CONTENT_DPATTACHMENTS_FIELD_ATTACHMENTS_NO_ITEM_MESSAGE');
		}

		$component = $app->bootComponent('dpattachments');
		if (!$component instanceof DPAttachmentsComponent) {
			return '';
		}

		$app->getDocument()->addStyleDeclaration('.com-dpattachments-layout-attachments__header { display: none }');

		return $component->render($this->form->getName(), $this->element['item_id']);
	}
}
