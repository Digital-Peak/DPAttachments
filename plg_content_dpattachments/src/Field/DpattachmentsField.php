<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2022 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

namespace DigitalPeak\Plugin\Content\DPAttachments\Field;

use DigitalPeak\Component\DPAttachments\Administrator\Extension\DPAttachmentsComponent;
use Joomla\CMS\Application\CMSWebApplicationInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;

class DpattachmentsField extends FormField
{
	public $element;
	public $form;
	protected $type = 'Dpattachments';

	protected function getInput()
	{
		$app = Factory::getApplication();

		if ((string)$this->element['item_id'] === '' || (string)$this->element['item_id'] === '0') {
			return $app->getLanguage()->_('PLG_CONTENT_DPATTACHMENTS_FIELD_ATTACHMENTS_NO_ITEM_MESSAGE');
		}

		$component = $app->bootComponent('dpattachments');
		if (!$component instanceof DPAttachmentsComponent || !$app instanceof CMSWebApplicationInterface) {
			return '';
		}

		$app->getDocument()->getWebAssetManager()->addInlineStyle('.com-dpattachments-layout-attachments__header { display: none }');

		return $component->render($this->form->getName(), (string)$this->element['item_id']);
	}
}
