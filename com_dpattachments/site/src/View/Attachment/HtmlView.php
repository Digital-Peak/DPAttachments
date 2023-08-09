<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2013 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

namespace DigitalPeak\Component\DPAttachments\Site\View\Attachment;

use DigitalPeak\Component\DPAttachments\Administrator\Model\AttachmentModel;
use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

class HtmlView extends BaseHtmlView
{
	protected $item;
	protected $params;
	protected $state;
	protected $user;
	protected $input;

	public function display($tpl = null)
	{
		$this->setModel(new AttachmentModel(), true);
		$app        = Factory::getApplication();
		$this->user = Factory::getUser();

		$this->input = $app->input;
		$this->item  = $this->get('Item');
		$this->state = $this->get('State');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			throw new Exception(implode('\n', $errors));
		}

		$this->params = $this->state->get('params');

		// Increment the hit counter of the attachment.
		$model = $this->getModel();
		$model->hit($this->item->id);
		$this->setLayout(strtolower(pathinfo($this->item->path, PATHINFO_EXTENSION)));

		parent::display($tpl);
	}
}
