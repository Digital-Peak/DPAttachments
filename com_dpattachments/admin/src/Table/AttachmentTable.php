<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2013 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

namespace DigitalPeak\Component\DPAttachments\Administrator\Table;

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

class AttachmentTable extends Table
{
	public function __construct(&$db)
	{
		parent::__construct('#__dpattachments', 'id', $db);

		$this->setColumnAlias('published', 'state');
	}

	public function bind($array, $ignore = '')
	{
		if (is_array($array) && isset($array['params']) && is_array($array['params'])) {
			$registry = new Registry();
			$registry->loadArray($array['params']);
			$array['params'] = (string)$registry;
		}

		return parent::bind($array, $ignore);
	}

	public function check()
	{
		if ($this->publish_down && $this->publish_down < $this->publish_up) {
			// Swap the dates.
			$temp               = $this->publish_up;
			$this->publish_up   = $this->publish_down;
			$this->publish_down = $temp;
		}

		if (empty($this->title)) {
			$this->title = str_replace([
				'_',
				'-',
				':'
			], ' ', $this->path);
		}

		$this->path = basename($this->path);

		if ($this->description === null) {
			$this->description = '';
		}

		if (empty($this->created) || $this->created === $this->getDbo()->getNullDate()) {
			$this->created = null;
		}
		if (empty($this->modified) || $this->modified === $this->getDbo()->getNullDate()) {
			$this->modified = null;
		}
		if (empty($this->publish_up) || $this->publish_up === $this->getDbo()->getNullDate()) {
			$this->publish_up = null;
		}
		if (empty($this->publish_down) || $this->publish_down === $this->getDbo()->getNullDate()) {
			$this->publish_down = null;
		}
		if (empty($this->checked_out_time) || $this->checked_out_time === $this->getDbo()->getNullDate()) {
			$this->checked_out_time = null;
		}

		if ($this->hits === '') {
			$this->hits = 0;
		}

		return true;
	}

	public function store($updateNulls = false)
	{
		$date = Factory::getDate();
		$user = Factory::getUser();

		if ($this->id) {
			// Existing item
			$this->modified    = $date->toSql();
			$this->modified_by = $user->get('id');
		} else {
			if (!(int)$this->created) {
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

	public function publish($pks = null, $state = 1, $userId = 0)
	{
		$k = $this->_tbl_key;

		// Sanitize input.
		$pks    = ArrayHelper::toInteger($pks);
		$userId = (int)$userId;
		$state  = (int)$state;

		// If there are no primary keys set check to see if the instance key is set
		if (empty($pks)) {
			if ($this->$k) {
				$pks = [$this->$k];
			} else {
				throw new Exception(Text::_('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED'));
			}
		}

		// Build the WHERE clause for the primary keys
		$where = $k . '=' . implode(' OR ' . $k . '=', $pks);

		// Determine if there is checkin support for the table
		if (!property_exists($this, 'checked_out') || !property_exists($this, 'checked_out_time')) {
			$checkin = '';
		} else {
			$checkin = ' AND (checked_out = 0 OR checked_out = ' . (int)$userId . ')';
		}

		// Update the publishing state for rows with the given primary keys
		$query = $this->_db->getQuery(true)
			->update($this->_db->quoteName($this->_tbl))
			->set($this->_db->quoteName('state') . ' = ' . (int)$state)
			->where('(' . $where . ')' . $checkin);
		$this->_db->setQuery($query);

		$this->_db->execute();

		// If checkin is supported and all rows were adjusted, check them in
		if ($checkin && (count($pks) == $this->_db->getAffectedRows())) {
			// Checkin the rows
			foreach ($pks as $pk) {
				$this->checkin($pk);
			}
		}

		// If the JTable instance value is in the list of primary keys that were set, set the instance
		if (in_array($this->$k, $pks)) {
			$this->state = $state;
		}

		return true;
	}
}
