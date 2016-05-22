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
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class listener implements EventSubscriberInterface
{
	protected $icons = array();

	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\cache\driver\driver_interface */
	protected $cache;

	public function __construct(
		\phpbb\auth\auth $auth,
		\phpbb\cache\service $cache)
	{
		$this->auth = $auth;
		$this->cache = $cache;
	}

	static public function getSubscribedEvents()
	{
		return array(
			'core.display_forums_modify_sql'			=> 'modify_sql',
			'core.display_forums_modify_forum_rows'		=> 'forums_modify_forum_rows',
			'core.display_forums_modify_template_vars'	=> 'forums_modify_template_vars',
		);
	}

	public function modify_sql($event)
	{
		$this->icons = $this->cache->obtain_icons();

		$sql_array = $event['sql_ary'];
		$sql_array['SELECT'] .= ', t.icon_id';
		$sql_array['LEFT_JOIN'][] = array('FROM' => array(TOPICS_TABLE => 't'), 'ON' => 'f.forum_last_post_id = t.topic_last_post_id');
		$event['sql_ary'] = $sql_array;
	}

	public function forums_modify_forum_rows($event)
	{
		$forum_rows = $event['forum_rows'];
		$parent_id = $event['parent_id'];
		$row = $event['row'];

		if ($row['forum_last_post_time'] >= $forum_rows[$parent_id]['forum_last_post_time'])
		{
			if ($row['enable_icons'] && !empty($row['icon_id']))
			{
				$forum_rows[$parent_id]['enable_icons'] = $row['enable_icons'];
				$forum_rows[$parent_id]['icon_id'] = $row['icon_id'];
			}
		}
		$event['forum_rows'] = $forum_rows;
	}

	public function forums_modify_template_vars($event)
	{
		$row = $event['row'];
		$template = $event['forum_row'];
		$forum_icon = array();

		if ($row['enable_icons'] && !empty($row['icon_id']) && $row['forum_password_last_post'] === '' && $this->auth->acl_get('f_read', $row['forum_id_last_post']))
		{
			$forum_icon = array(
				'TOPIC_ICON_IMG' 		=> $this->icons[$row['icon_id']]['img'],
				'TOPIC_ICON_IMG_WIDTH'	=> $this->icons[$row['icon_id']]['width'],
				'TOPIC_ICON_IMG_HEIGHT'	=> $this->icons[$row['icon_id']]['height'],
				'TOPIC_ICON_ALT'		=> !empty($this->icons[$row['icon_id']]['alt']) ? $this->icons[$row['icon_id']]['alt'] : '',
			);
		}

		$event['forum_row'] = array_merge($template, $forum_icon);
	}
}
