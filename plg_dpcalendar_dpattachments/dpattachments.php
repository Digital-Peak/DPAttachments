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

class plgDPCalendarDpattachments extends JPlugin {

    public function onEventAfterDisplay($event, $output) {
        return DPAttachmentsCore::render('com_dpcalendar.event', $event->id);
    }

    public function onEventAfterDelete($event) {
        return DPAttachmentsCore::delete('com_dpcalendar.event', $event->id);
    }
}