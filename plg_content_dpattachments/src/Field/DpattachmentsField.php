<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2022 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

namespace DigitalPeak\Plugin\Content\DPAttachments\Field;

use Joomla\CMS\Application\CMSWebApplicationInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;

class DpattachmentsField extends FormField
{
	protected $type = 'Dpattachments';

	protected function getInput(): string
	{
		$app = Factory::getApplication();
		if (!$app instanceof CMSWebApplicationInterface) {
			return '';
		}

		$app->getDocument()->getWebAssetManager()->addInlineStyle('.com-dpattachments-layout-attachments__header { display: none }');

		return $app->bootComponent('dpattachments')->render($this->form->getName(), (string)$this->element['item_id']);
	}
}
