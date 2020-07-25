<?php

/**
*
* Topic icons on index extension for the phpBB Forum Software package.
*
* @copyright (c) 2016 Rich McGirr (RMcGirr83)
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace rmcgirr83\topicicononindex\event;

/**
* Event listener
*/
use phpbb\auth\auth;
use phpbb\cache\service as cache_service;
use phpbb\db\driver\driver_interface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class listener implements EventSubscriberInterface
{
	/** @var \auth */
	protected $auth;

	/** @var cache_service */
	protected $cache;

	/** @var driver_interface */
	protected $db;

	/**
	* Constructor
	*
	* @param \phpbb\auth\auth
	* @param cache_service
	* @param \phpbb\db\driver\driver_interface
	* @return \rmcgirr83\topicicononindex\event\listener
	* @access public
	*/
	public function __construct(
		auth $auth,
		cache_service $cache,
		driver_interface $db)
	{
		$this->auth = $auth;
		$this->cache = $cache;
		$this->db = $db;

		$this->icons = $this->cache->obtain_icons();
		$this->forum_topic_icons = $this->get_topic_icons();
	}

	/**
	* Assign functions defined in this class to event listeners in the core
	*
	* @return array
	* @static
	* @access public
	*/
	static public function getSubscribedEvents()
	{
		return array(
			'core.display_forums_modify_template_vars'	=> 'forums_modify_template_vars',
		);
	}

	/**
	* generate cache of forum topic icons
	*
	* @return array
	* @access private
	*/
	private function get_topic_icons()
	{
		if (($topic_icons = $this->cache->get('_forum_topic_ids')) === false)
		{
			$sql = 'SELECT topic_last_post_id, icon_id
				FROM ' . TOPICS_TABLE . '
				WHERE icon_id <> 0';
			$result = $this->db->sql_query($sql);

			$topic_icons = array();
			while ($row = $this->db->sql_fetchrow($result))
			{
				$topic_icons[$row['topic_last_post_id']] = $row['icon_id'];
			}
			$this->db->sql_freeresult($result);

			$this->cache->put('_forum_topic_ids', $topic_icons, 300);
		}

		return $topic_icons;
	}

	/**
	 * Show the topic icon if authed to read the forum
	 *
	 * @event	object $event	The event object
	 * @return	null
	 * @access	public
	 */
	public function forums_modify_template_vars($event)
	{
		$topic_icons = $this->forum_topic_icons;
		$row = $event['row'];
		$template = $event['forum_row'];
		$forum_icon = array();

		if ($row['enable_icons'] && $row['forum_password_last_post'] === '' && $this->auth->acl_get('f_read', $row['forum_id']))
		{
			if (in_array($row['forum_last_post_id'], array_keys($topic_icons)))
			{
				$icon_id = $topic_icons[$row['forum_last_post_id']];

				$forum_icon = array(
					'TOPIC_ICON_IMG' 		=> $this->icons[$icon_id]['img'],
					'TOPIC_ICON_IMG_WIDTH'	=> $this->icons[$icon_id]['width'],
					'TOPIC_ICON_IMG_HEIGHT'	=> $this->icons[$icon_id]['height'],
					'TOPIC_ICON_ALT'		=> !empty($this->icons[$icon_id]['alt']) ? $this->icons[$icon_id]['alt'] : '',
				);
			}
		}

		$event['forum_row'] = array_merge($template, $forum_icon);
	}
}
