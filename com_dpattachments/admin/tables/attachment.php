<?php
/**
 * @package		DPAttachments
 * @author		Digital Peak http://www.digital-peak.com
 * @copyright	Copyright (C) 2012 - 2013 Digital Peak. All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class DPAttachmentsTableAttachment extends JTable {

    public function __construct(&$db) {
        parent::__construct('#__dpattachments', 'id', $db);
    }

    public function check() {
        if ($this->publish_down > $this->_db->getNullDate() && $this->publish_down < $this->publish_up) {
            // Swap the dates.
            $temp = $this->publish_up;
            $this->publish_up = $this->publish_down;
            $this->publish_down = $temp;
        }

        if (empty($this->title)) {
            $this->title = str_replace(array('_', '-', ':'), ' ', $this->path);
        }

        return true;
    }

    public function store($updateNulls = false) {
        $date = JFactory::getDate();
        $user = JFactory::getUser();

        if ($this->id) {
            // Existing item
            $this->modified = $date->toSql();
            $this->modified_by = $user->get('id');
        } else {
            if (! (int)$this->created) {
                $this->created = $date->toSql();
            }

            if (empty($this->created_by)) {
                $this->created_by = $user->get('id');
            }
            $this->created_ip = $_SERVER['REMOTE_ADDR'];

            $this->state = 1;
        }

        return parent::store($updateNulls);
    }

    public function publish($pks = null, $state = 1, $userId = 0) {
        $k = $this->_tbl_key;

        // Sanitize input.
        JArrayHelper::toInteger($pks);
        $userId = (int)$userId;
        $state = (int)$state;

        // If there are no primary keys set check to see if the instance key is set.
        if (empty($pks)) {
            if ($this->$k) {
                $pks = array($this->$k);
            }             // Nothing to set publishing state on, return false.
            else {
                $this->setError(JText::_('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED'));
                return false;
            }
        }

        // Build the WHERE clause for the primary keys.
        $where = $k . '=' . implode(' OR ' . $k . '=', $pks);

        // Determine if there is checkin support for the table.
        if (! property_exists($this, 'checked_out') || ! property_exists($this, 'checked_out_time')) {
            $checkin = '';
        } else {
            $checkin = ' AND (checked_out = 0 OR checked_out = ' . (int)$userId . ')';
        }

        // Update the publishing state for rows with the given primary keys.
        $query = $this->_db->getQuery(true)->update($this->_db->quoteName($this->_tbl))->set($this->_db->quoteName('state') . ' = ' . (int)$state)->where('(' . $where .
                 ')' . $checkin);
        $this->_db->setQuery($query);

        try {
            $this->_db->execute();
        } catch ( RuntimeException $e ) {
            $this->setError($e->getMessage());
            return false;
        }

        // If checkin is supported and all rows were adjusted, check them in.
        if ($checkin && (count($pks) == $this->_db->getAffectedRows())) {
            // Checkin the rows.
            foreach ( $pks as $pk ) {
                $this->checkin($pk);
            }
        }

        // If the JTable instance value is in the list of primary keys that were set, set the instance.
        if (in_array($this->$k, $pks)) {
            $this->state = $state;
        }

        $this->setError('');

        return true;
    }
}
