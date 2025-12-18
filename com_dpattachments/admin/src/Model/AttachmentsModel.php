<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2013 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

namespace DigitalPeak\Component\DPAttachments\Administrator\Model;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

class AttachmentsModel extends ListModel
{
	public $context;

	public $state;

	public function __construct($config = [], ?MVCFactoryInterface $factory = null)
	{
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = [
				'id',
				'a.id',
				'title',
				'a.title',
				'checked_out',
				'a.checked_out',
				'checked_out_time',
				'a.checked_out_time',
				'state',
				'a.state',
				'access',
				'a.access',
				'access_level',
				'created',
				'a.created',
				'created_by',
				'a.created_by',
				'created_by_alias',
				'a.created_by_alias',
				'hits',
				'a.hits',
				'publish_up',
				'a.publish_up',
				'publish_down',
				'a.publish_down'
			];
		}

		parent::__construct($config, $factory);
	}

	protected function populateState($ordering = null, $direction = null)
	{
		$app = Factory::getApplication();
		if (!$app instanceof CMSApplication) {
			return;
		}

		// Adjust the context to support modal layouts.
		if ($layout = $app->input->get('layout')) {
			$this->context .= '.' . $layout;
		}

		$item = $this->getUserStateFromRequest($this->context . '.filter.item', 'item_id');
		$this->setState('filter.item', $item);

		$context = $this->getUserStateFromRequest($this->context . '.filter.context', 'context');
		$this->setState('filter.context', $context);

		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$access = $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access', null, 'int');
		$this->setState('filter.access', $access);

		$authorId = $app->getUserStateFromRequest($this->context . '.filter.author_id', 'filter_author_id');
		$this->setState('filter.author_id', $authorId);

		$state = $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', '');
		$this->setState('filter.state', $state);

		$params = ComponentHelper::getParams('com_dpattachments');
		if ($app instanceof SiteApplication) {
			$params = $app->getParams();
		}
		$this->setState('params', $params);

		// List state information.
		parent::populateState('a.created', 'asc');
	}

	public function getItems()
	{
		$items = parent::getItems();

		foreach ($items as $attachment) {
			$attachment->params = new Registry($attachment->params);
		}

		return $items;
	}

	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.access');
		$id .= ':' . $this->getState('filter.state');
		$id .= ':' . $this->getState('filter.author_id');

		return parent::getStoreId($id);
	}

	protected function getListQuery()
	{
		// Create a new query object.
		$db    = $this->getDatabase();
		$query = $db->getQuery(true);
		$user  = $this->getCurrentUser();
		$app   = Factory::getApplication();

		// Select the required fields from the table.
		$query->select($this->getState('list.select', 'a.*'));
		$query->from('#__dpattachments AS a');

		// Join over the users for the checked out user.
		$query->select('uc.name AS editor')->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

		// Join over the asset groups.
		$query->select('ag.title AS access_level')->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');

		// Join over the users for the author.
		$query->select('ua.id as author_id, ua.name AS author_name, ua.email as author_email')->join(
			'LEFT',
			'#__users AS ua ON ua.id = a.created_by'
		);

		// Get contact id
		$subQuery = $db->getQuery(true)
			->select('MAX(contact.id) AS id')
			->from('#__contact_details AS contact')
			->where('contact.published = 1')
			->where('contact.user_id = a.created_by');

		// Filter by language
		if ($this->getState('filter.language')) {
			$subQuery->where(
				'(contact.language in (' . $db->quote($app->getLanguage()->getTag()) . ',' . $db->quote('*') . ') OR contact.language IS NULL)'
			);
		}
		$query->select('(' . $subQuery . ') as contactid');

		// Filter by access level.
		if ($access = $this->getState('filter.access')) {
			$query->where('a.access = ' . (int)$access);
		}

		// Implement View Level Access
		if (!$user->authorise('core.admin')) {
			$groups = implode(',', $user->getAuthorisedViewLevels());
			$query->where('a.access IN (' . $groups . ')');
		}

		// Filter by state state
		$state = $this->getState('filter.state');
		if (\is_array($state)) {
			$state = ArrayHelper::toInteger($state);
			$query->where('a.state in (' . implode(',', $state) . ')');
		} elseif (is_numeric($state)) {
			$query->where('a.state = ' . (int)$state);
		} elseif ($state === '') {
			$query->where('a.state in (0,1,2)');
		}

		// Filter by author
		$authorId = $this->getState('filter.author_id');
		if (is_numeric($authorId) && $authorId > 0) {
			$type = $this->getState('filter.author_id.include', true) ? '= ' : '<>';
			$query->where('a.created_by ' . $type . (int)$authorId);
		}

		// Filter by search in title.
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			if (stripos((string)$search, 'id:') === 0) {
				$query->where('a.id = ' . (int)substr((string)$search, 3));
			} elseif (stripos((string)$search, 'author:') === 0) {
				$search = $db->quote('%' . $db->escape(substr((string)$search, 7), true) . '%');
				$query->where('(ua.name LIKE ' . $search . ' OR ua.username LIKE ' . $search . ')');
			} else {
				$search = $db->quote('%' . $db->escape($search, true) . '%');
				$query->where('(a.title LIKE ' . $search . ' or a.description like ' . $search . ')');
			}
		}

		$item = $this->getState('filter.item');
		if ($item) {
			$query->where('a.item_id = ' . $db->quote($item));
		}
		$context = $this->getState('filter.context');
		if ($context) {
			$query->where('a.context = ' . $db->quote($context));
		}

		if ($app instanceof SiteApplication) {
			// Filter by start and end dates.
			$nowDate = $db->quote(Factory::getDate()->toSql());

			$query->where('(a.publish_up IS NULL OR a.publish_up is null OR a.publish_up <= ' . $nowDate . ')');
			$query->where('(a.publish_down IS NULL OR a.publish_down is null OR a.publish_down >= ' . $nowDate . ')');
		}

		// Add the list ordering clause.
		$orderCol  = $this->state->get('list.ordering', 'a.created');
		$orderDirn = $this->state->get('list.direction', 'asc');

		if ($orderCol == 'access_level') {
			$orderCol = 'ag.title';
		}
		$query->order($db->escape($orderCol . ' ' . $orderDirn));

		// Echo nl2br(str_replace('#__', 'a_', $query));die();
		return $query;
	}

	public function getAuthors(): array
	{
		// Create a new query object.
		$db    = $this->getDatabase();
		$query = $db->getQuery(true);

		// Construct the query
		$query->select('u.id AS value, u.name AS text')
			->from('#__users AS u')
			->join('INNER', '#__dpattachments AS c ON c.created_by = u.id')
			->group('u.id, u.name')
			->order('u.name');

		// Setup the query
		$db->setQuery($query);

		// Return the result
		return $db->loadObjectList();
	}
}
