<?php
/**
 * @package		DPAttachments
 * @author		Digital Peak http://www.digital-peak.com
 * @copyright	Copyright (C) 2012 - 2013 Digital Peak. All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

JLoader::import('components.com_dpattachments.libraries.dpattachments.core', JPATH_ADMINISTRATOR);

// If the component is not installed we fail here and no error is thrown
if (! class_exists('DPAttachmentsCore')) {
    return;
}

class plgSystemDpattachments extends JPlugin {

    public function onContentAfterDisplay($context, $item, $params) {
        return DPAttachmentsCore::render($context, (int)$item->id);
    }

    public function onContentAfterDelete($context, $item) {
        return DPAttachmentsCore::delete($context, (int)$item->id);
    }

    public function onEventAfterDisplay($event, $output) {
        return DPAttachmentsCore::render('com_dpcalendar.event', $event->id);
    }

    public function onEventAfterDelete($event) {
        return DPAttachmentsCore::delete('com_dpcalendar.event', $event->id);
    }
}