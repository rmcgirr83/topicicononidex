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

	static public function getSubscribedEvents()
	{
		return array(
			'core.display_forums_modify_template_vars'	=> 'forums_modify_template_vars',
		);
	}

	private function get_topic_icons()
	{

		$sql = 'SELECT topic_last_post_id, icon_id
			FROM ' . TOPICS_TABLE . '
			WHERE icon_id <> 0';
		$result = $this->db->sql_query($sql, 300);

		$topic_icons = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			$topic_icons[$row['topic_last_post_id']] = $row['icon_id'];
		}
		$this->db->sql_freeresult($result);

		return $topic_icons;
	}

	public function forums_modify_template_vars($event)
	{
		$topic_icons = $this->forum_topic_icons;
		$row = $event['row'];
		$template = $event['forum_row'];
		$forum_icon = array();

		if ($row['enable_icons'] && $row['forum_password_last_post'] === '' && $this->auth->acl_get('f_read', $row['forum_id_last_post']))
		{
			foreach ($topic_icons as $key => $value)
			{
				if ($row['forum_last_post_id'] == $key)
				{
					$forum_icon = array(
						'TOPIC_ICON_IMG' 		=> $this->icons[$value]['img'],
						'TOPIC_ICON_IMG_WIDTH'	=> $this->icons[$value]['width'],
						'TOPIC_ICON_IMG_HEIGHT'	=> $this->icons[$value]['height'],
						'TOPIC_ICON_ALT'		=> !empty($this->icons[$value]['alt']) ? $this->icons[$value]['alt'] : '',
					);
				}
			}
		}

		$event['forum_row'] = array_merge($template, $forum_icon);
	}
}
