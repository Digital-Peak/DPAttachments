<?php
/**
 * @package    DPAttachments
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2012 - 2018 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class DPAttachmentsModelAttachments extends JModelList
{

	public function __construct($config = array())
	{
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
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
			);
		}

		parent::__construct($config);
	}

	protected function populateState($ordering = null, $direction = null)
	{
		$app = JFactory::getApplication();

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

		$access = $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access', 0, 'int');
		$this->setState('filter.access', $access);

		$authorId = $app->getUserStateFromRequest($this->context . '.filter.author_id', 'filter_author_id');
		$this->setState('filter.author_id', $authorId);

		$published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);

		$params = JComponentHelper::getParams('com_dpattachments');
		if ($app->isSite()) {
			$params = $app->getParams();
		}
		$this->setState('params', $params);

		// List state information.
		parent::populateState('a.created', 'asc');
	}

	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.access');
		$id .= ':' . $this->getState('filter.published');
		$id .= ':' . $this->getState('filter.author_id');

		return parent::getStoreId($id);
	}

	protected function getListQuery()
	{
		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);
		$user  = JFactory::getUser();
		$app   = JFactory::getApplication();

		// Select the required fields from the table.
		$query->select(
			$this->getState('list.select',
				'a.id, a.title, a.description, a.path, a.size, a.checked_out, a.checked_out_time, a.context, a.item_id' .
				', a.state, a.access, a.created, a.created_by, a.created_by_alias, a.modified, a.publish_up, a.publish_down, a.hits'));
		$query->from('#__dpattachments AS a');

		// Join over the users for the checked out user.
		$query->select('uc.name AS editor')->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

		// Join over the asset groups.
		$query->select('ag.title AS access_level')->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');

		// Join over the users for the author.
		$query->select('ua.id as author_id, ua.name AS author_name, ua.email as author_email')->join('LEFT',
			'#__users AS ua ON ua.id = a.created_by');

		// Get contact id
		$subQuery = $db->getQuery(true)
			->select('MAX(contact.id) AS id')
			->from('#__contact_details AS contact')
			->where('contact.published = 1')
			->where('contact.user_id = a.created_by');

		// Filter by language
		if ($this->getState('filter.language')) {
			$subQuery->where(
				'(contact.language in (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ') OR contact.language IS NULL)');
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

		// Filter by published state
		$published = $this->getState('filter.published');
		if (is_array($published)) {
			JArrayHelper::toInteger($published);
			$query->where('a.state in (' . implode(',', $published) . ')');
		} else if (is_numeric($published)) {
			$query->where('a.state = ' . (int)$published);
		} else if ($published === '') {
			$query->where('a.state = 1');
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
			if (stripos($search, 'id:') === 0) {
				$query->where('a.id = ' . (int)substr($search, 3));
			} else if (stripos($search, 'author:') === 0) {
				$search = $db->quote('%' . $db->escape(substr($search, 7), true) . '%');
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

		if ($app->isSite()) {
			// Filter by start and end dates.
			$nullDate = $db->quote($db->getNullDate());
			$nowDate  = $db->quote(JFactory::getDate()->toSql());

			$query->where('(a.publish_up = ' . $nullDate . ' OR a.publish_up <= ' . $nowDate . ')');
			$query->where('(a.publish_down = ' . $nullDate . ' OR a.publish_down >= ' . $nowDate . ')');
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

	public function getAuthors()
	{
		// Create a new query object.
		$db    = $this->getDbo();
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

	public function getItems()
	{
		$items  = parent::getItems();
		$user   = JFactory::getUser();
		$userId = $user->get('id');
		$guest  = $user->get('guest');
		$groups = $user->getAuthorisedViewLevels();

		foreach ($items as $comment) {
			$comment->params = clone $this->getState('params');
			if ($comment->modified == $this->getDbo()->getNullDate()) {
				$comment->modified = $comment->created;
			}
			// Get display date
			switch ($comment->params->get('list_show_date')) {
				case 'modified':
					$comment->displayDate = $comment->modified;
					break;

				case 'published':
					$comment->displayDate = ($comment->publish_up == 0) ? $comment->created : $comment->publish_up;
					break;

				default:
				case 'created':
					$comment->displayDate = $comment->created;
					break;
			}
		}

		return $items;
	}
}
