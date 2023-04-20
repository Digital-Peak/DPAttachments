<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2020 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace Tests\Support\Helper;

use Codeception\Module\Db;

class JoomlaDb extends Db
{
	protected $prefix;

	public function _initialize(): void
	{
		$this->prefix = (isset($this->config['prefix'])) ? $this->config['prefix'] : '';

		parent::_initialize();
	}

	public function deleteFromDatabase($table, $criteria)
	{
		$table = $this->addPrefix($table);

		$this->driver->deleteQueryByCriteria($table, $criteria);
	}

	public function updateInDatabase($table, array $data, array $criteria = []): void
	{
		$table = $this->addPrefix($table);

		parent::updateInDatabase($table, $data, $criteria);
	}

	public function haveInDatabase($table, array $data): int
	{
		$table = $this->addPrefix($table);

		return parent::haveInDatabase($table, $data);
	}

	public function seeInDatabase($table, $criteria = []): void
	{
		$table = $this->addPrefix($table);

		parent::seeInDatabase($table, $criteria);
	}

	public function dontSeeInDatabase($table, $criteria = []): void
	{
		$table = $this->addPrefix($table);

		parent::dontSeeInDatabase($table, $criteria);
	}

	public function grabFromDatabase($table, $column, $criteria = null)
	{
		$table = $this->addPrefix($table);

		return parent::grabFromDatabase($table, $column, $criteria);
	}

	public function seeNumRecords($expectedNumber, $table, array $criteria = []): void
	{
		$table = $this->addPrefix($table);

		parent::seeNumRecords($expectedNumber, $table, $criteria);
	}

	public function grabNumRecords($table, array $criteria = []): int
	{
		$table = $this->addPrefix($table);

		return parent::grabNumRecords($table, $criteria);
	}

	protected function addPrefix($table)
	{
		return $this->prefix . $table;
	}
}
