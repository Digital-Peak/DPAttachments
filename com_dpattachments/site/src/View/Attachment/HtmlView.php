<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2013 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

namespace DigitalPeak\Component\DPAttachments\Site\View\Attachment;

use DigitalPeak\Component\DPAttachments\Administrator\Model\AttachmentModel;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\Input\Input;
use Joomla\Registry\Registry;

class HtmlView extends BaseHtmlView
{
	/** @var \stdClass */
	protected $item;

	/** @var Registry */
	protected $params;

	/** @var Registry */
	protected $state;

	/** @var Input */
	protected $input;

	public function display($tpl = null): void
	{
		$this->setModel(new AttachmentModel(), true);
		$app = Factory::getApplication();

		if ($app instanceof CMSApplication) {
			$this->input = $app->input;
		}
		$this->item  = $this->get('Item');
		$this->state = $this->get('State');

		// Check for errors.
		if (count($errors = $this->get('Errors')) > 0) {
			throw new \Exception(implode('\n', $errors));
		}

		$this->params = $this->state->get('params');

		$model = $this->getModel();
		$model->hit($this->item->id);
		$this->setLayout(strtolower(pathinfo($this->item->path, PATHINFO_EXTENSION)));

		require_once JPATH_ADMINISTRATOR . '/components/com_dpattachments/vendor/autoload.php';

		parent::display($tpl);
	}
}
