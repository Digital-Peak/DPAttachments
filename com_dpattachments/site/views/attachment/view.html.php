<?php
/**
 * @package		DPAttachments
 * @author		Digital Peak http://www.digital-peak.com
 * @copyright	Copyright (C) 2012 - 2013 Digital Peak. All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class DPAttachmentsViewAttachment extends JViewLegacy {

    protected $item;

    protected $params;

    protected $state;

    protected $user;

    public function display($tpl = null) {
        JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_dpattachments/models', 'DPAttachmentsModel');
        $this->setModel(JModelLegacy::getInstance('Attachment', 'DPAttachmentsModel'), true);
        $app = JFactory::getApplication();
        $user = JFactory::getUser();

        $this->item = $this->get('Item');
        $item = $this->item;
        $this->state = $this->get('State');
        $this->user = $user;

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JError::raiseWarning(500, implode("\n", $errors));
            return false;
        }

        $this->params = $this->state->get('params');
        $active = $app->getMenu()->getActive();
        $temp = clone ($this->params);

        // Check to see which parameters should take priority
        if ($active) {
            $currentLink = $active->link;

            // If the current view is the active item and an dpcase view for this dpcase, then the menu item params take priority
            if (strpos($currentLink, 'view=case') && (strpos($currentLink, '&id=' . (string)$item->id))) {
                // Load layout from active query (in case it is an alternative menu item)
                if (isset($active->query['layout'])) {
                    $this->setLayout($active->query['layout']);
                }                 // Check for alternative layout of dpcase
                elseif ($layout = $item->params->get('case_layout')) {
                    $this->setLayout($layout);
                }

                // $item->params are the dpcase params, $temp are the menu item params
                // Merge so that the menu item params take priority
                $item->params->merge($temp);
            } else {
                // Current view is not a single dpcase, so the dpcase params take priority here
                // Merge the menu item params with the dpcase params so that the dpcase params take priority
                $temp->merge($item->params);
                $item->params = $temp;

                // Check for alternative layouts (since we are not in a single-dpcase menu item)
                // Single-dpcase menu item layout takes priority over alt layout for an dpcase
                if ($layout = $item->params->get('case_layout')) {
                    $this->setLayout($layout);
                }
            }
        }

        // Increment the hit counter of the attachment.
        $model = $this->getModel();
        $model->hit($item->id);
        $this->setLayout(strtolower(JFile::getExt($this->item->path)));

        parent::display($tpl);
    }
}
