<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2013 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

namespace DigitalPeak\Component\DPAttachments\Site\View\Form;

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\Registry\Registry;

class HtmlView extends BaseHtmlView
{
	/** @var string */
	protected $pageclass_sfx;

	/** @var Registry */
	protected $params;

	/** @var Form */
	protected $form;

	/** @var \stdClass */
	protected $item;

	/** @var string */
	protected $return_page;

	/** @var Registry */
	protected $state;

	public function display($tpl = null): void
	{
		// Get model data
		$this->state       = $this->getModel()->getState();
		$this->item        = $this->getModel()->getItem() ?: new \stdClass();
		$this->form        = $this->getModel()->getForm();
		$this->return_page = $this->getModel()->getReturnPage();

		$authorised = false;
		if (!empty($this->item->params) && $this->item->params instanceof Registry) {
			$authorised = $this->item->params->get('access-edit');
		}

		if ($authorised !== true) {
			throw new \Exception(Text::_('JERROR_ALERTNOAUTHOR'));
		}

		$this->item->tags = new TagsHelper();
		if (!empty($this->item->id)) {
			$this->item->tags->getItemTags('com_dpattachments.attachment.', $this->item->id);
		}

		// Check for errors
		if ($errors = $this->getModel()->getErrors()) {
			throw new \Exception(implode('\n', $errors));
		}

		// Create a shortcut to the parameters
		$params = &$this->state->params;

		// Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars((string)$params->get('pageclass_sfx', ''));

		$this->params = $params;

		$this->form->setFieldAttribute('tags', 'mode', 'nested');
		$this->prepareDocument();

		parent::display($tpl);
	}

	protected function prepareDocument(): void
	{
		$app = Factory::getApplication();
		if (!$app instanceof SiteApplication) {
			return;
		}

		$menus = $app->getMenu();
		$title = null;

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();
		if ($menu !== null) {
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		} else {
			$this->params->def('page_heading', Text::_('COM_DPATTACHMENTS_FORM_EDIT_ATTACHMENT'));
		}

		$title = $this->params->def('page_title', Text::_('COM_DPATTACHMENTS_FORM_EDIT_ATTACHMENT'));
		if ($app->get('sitename_pagetitles', 0) == 1) {
			$title = Text::sprintf('JPAGETITLE', $app->get('sitename'), $title);
		} elseif ($app->get('sitename_pagetitles', 0) == 2) {
			$title = Text::sprintf('JPAGETITLE', $title, $app->get('sitename'));
		}

		$document = $this->getDocument();
		$document->setTitle($title);

		$pathway = $app->getPathWay();
		$pathway->addItem($title, '');

		if ($this->params->get('menu-meta_description')) {
			$document->setDescription($this->params->get('menu-meta_description'));
		}

		if ($this->params->get('menu-meta_keywords')) {
			$document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}

		if ($this->params->get('robots')) {
			$document->setMetadata('robots', $this->params->get('robots'));
		}
	}
}
