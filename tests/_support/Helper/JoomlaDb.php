<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2020 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace Helper;

use Codeception\Module\Db;

class JoomlaDb extends Db
{
	protected $prefix;

	public function _initialize()
	{
		$this->prefix = (isset($this->config['prefix'])) ? $this->config['prefix'] : '';

		return parent::_initialize();
	}

	public function deleteFromDatabase($table, $criteria)
	{
		$table = $this->addPrefix($table);

		$this->driver->deleteQueryByCriteria($table, $criteria);
	}

	public function updateInDatabase($table, array $data, array $criteria = [])
	{
		$table = $this->addPrefix($table);

		return parent::updateInDatabase($table, $data, $criteria);
	}

	public function haveInDatabase($table, array $data)
	{
		$table = $this->addPrefix($table);

		return parent::haveInDatabase($table, $data);
	}

	public function seeInDatabase($table, $criteria = [])
	{
		$table = $this->addPrefix($table);

		return parent::seeInDatabase($table, $criteria);
	}

	public function dontSeeInDatabase($table, $criteria = [])
	{
		$table = $this->addPrefix($table);

		return parent::dontSeeInDatabase($table, $criteria);
	}

	public function grabFromDatabase($table, $column, $criteria = null)
	{
		$table = $this->addPrefix($table);

		return parent::grabFromDatabase($table, $column, $criteria);
	}

	public function seeNumRecords($expectedNumber, $table, array $criteria = [])
	{
		$table = $this->addPrefix($table);

		return parent::seeNumRecords($expectedNumber, $table, $criteria);
	}

	public function grabNumRecords($table, array $criteria = [])
	{
		$table = $this->addPrefix($table);

		return parent::grabNumRecords($table, $criteria);
	}

	protected function addPrefix($table)
	{
		return $this->prefix . $table;
	}
}
