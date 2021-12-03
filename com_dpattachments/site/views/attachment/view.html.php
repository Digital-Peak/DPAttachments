<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2013 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\Registry\Registry;

class DPAttachmentsViewAttachment extends HtmlView
{
	protected $item;
	protected $params;
	protected $state;
	protected $user;

	public function display($tpl = null)
	{
		BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_dpattachments/models', 'DPAttachmentsModel');
		$this->setModel(BaseDatabaseModel::getInstance('Attachment', 'DPAttachmentsModel'), true);
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
		$active       = $app->getMenu()->getActive();
		$temp         = clone ($this->params);

		// Check to see which parameters should take priority
		if ($active) {
			$currentLink = $active->link;

			// If the current view is the active item and an dpcase view for
			// this dpcase, then the menu item params take priority
			if (strpos($currentLink, 'view=case') && (strpos($currentLink, '&id=' . (string)$this->item->id))) {
				// Load layout from active query (in case it is an alternative
				// menu item)
				if (isset($active->query['layout'])) {
					$this->setLayout($active->query['layout']);
				} elseif ($layout = $this->item->params->get('case_layout')) {
					$this->setLayout($layout);
				}

				$this->item->params->merge($temp);
			} else {
				$temp->merge(!empty($this->item->params) ?: new Registry());
				$this->item->params = $temp;

				if ($layout = $this->item->params->get('case_layout')) {
					$this->setLayout($layout);
				}
			}
		}

		// Increment the hit counter of the attachment.
		$model = $this->getModel();
		$model->hit($this->item->id);
		$this->setLayout(strtolower(pathinfo($this->item->path, PATHINFO_EXTENSION)));

		parent::display($tpl);
	}
}
