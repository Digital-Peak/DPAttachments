<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2013 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

namespace DigitalPeak\Component\DPAttachments\Administrator\Table;

use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\User\CurrentUserInterface;
use Joomla\CMS\User\CurrentUserTrait;
use Joomla\Database\DatabaseDriver;
use Joomla\Registry\Registry;

class AttachmentTable extends Table implements CurrentUserInterface
{
	use CurrentUserTrait;

	/** @var ?string */
	public $publish_down;

	/** @var ?string */
	public $publish_up;

	/** @var string */
	public $title;

	/** @var string */
	public $context;

	/** @var string */
	public $item_id;

	/** @var string */
	public $path;

	/** @var string */
	public $description;

	/** @var ?string */
	public $created;

	/** @var int */
	public $created_by;

	/** @var ?string */
	public $modified;

	/** @var int */
	public $modified_by;

	/** @var ?string */
	public $checked_out_time;

	/** @var int */
	public $hits;

	/** @var int */
	public $id;

	/** @var string */
	public $created_ip;

	/** @var int */
	public $state;

	/** @var int */
	public $version;

	/** @var string */
	public $_tbl_key;

	/** @var string */
	public $_tbl;

	public function __construct(DatabaseDriver &$db)
	{
		parent::__construct('#__dpattachments', 'id', $db);

		$this->setColumnAlias('published', 'state');
	}

	public function bind($array, $ignore = '')
	{
		if (\is_array($array) && isset($array['params']) && \is_array($array['params'])) {
			$registry = new Registry();
			$registry->loadArray($array['params']);
			$array['params'] = (string)$registry;
		}

		return parent::bind($array, $ignore);
	}

	public function check(): bool
	{
		if ($this->publish_down && $this->publish_up && $this->publish_down < $this->publish_up) {
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

		if (empty($this->created)) {
			$this->created = null;
		}
		if (empty($this->modified)) {
			$this->modified = null;
		}
		if (empty($this->publish_up)) {
			$this->publish_up = null;
		}
		if (empty($this->publish_down)) {
			$this->publish_down = null;
		}
		if (empty($this->checked_out_time)) {
			$this->checked_out_time = null;
		}

		if ((int)$this->hits === 0) {
			$this->hits = 0;
		}

		return true;
	}

	public function store($updateNulls = false)
	{
		$date = Factory::getDate();
		$user = $this->getCurrentUser();

		if ((int)$this->id !== 0) {
			// Existing item
			$this->modified    = $date->toSql();
			$this->modified_by = $user->id;
		} else {
			if ((int)$this->created === 0) {
				$this->created = $date->toSql();
			}

			if (empty($this->created_by)) {
				$this->created_by = $user->id;
			}
			$this->created_ip = $_SERVER['REMOTE_ADDR'];

			$this->state = 1;
		}

		return parent::store($updateNulls);
	}
}
