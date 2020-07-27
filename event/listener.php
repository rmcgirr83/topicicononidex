<?php

/**
*
* Topic icon on index extension for the phpBB Forum Software package.
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
use phpbb\language\language;
use phpbb\template\template;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class listener implements EventSubscriberInterface
{
	/** @var auth */
	protected $auth;

	/** @var cache_service */
	protected $cache;

	/** @var driver_interface */
	protected $db;

	/** @var language */
	protected $language;

	/** @var template */
	protected $template;

	/**
	* Constructor
	*
	* @param \phpbb\auth\auth					$auth		Auth object
	* @param cache_service						$cache		Cache object
	* @param \phpbb\db\driver\driver_interface	$db			Database object
	* @param \phpbb\language\language			$language	Lanugage object
	* @param \phbb\template\template			$template	Template object
	* @return \rmcgirr83\topicicononindex\event\listener
	* @access public
	*/
	public function __construct(
		auth $auth,
		cache_service $cache,
		driver_interface $db,
		language $language,
		template $template)
	{
		$this->auth = $auth;
		$this->cache = $cache;
		$this->db = $db;
		$this->language = $language;
		$this->template = $template;

		$this->icons = $this->cache->obtain_icons();
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
			'core.acp_extensions_run_action_after'	=> 'acp_extensions_run_action_after',
			'core.display_forums_modify_template_vars'	=> 'forums_modify_template_vars',
		);
	}

	/* Display additional metdate in extension details
	*
	* @param $event			event object
	* @param return null
	* @access public
	*/
	public function acp_extensions_run_action_after($event)
	{
		if ($event['ext_name'] == 'rmcgirr83/topicicononindex' && $event['action'] == 'details')
		{
			$this->language->add_lang('common', $event['ext_name']);
			$this->template->assign_vars([
				'L_BUY_ME_A_BEER_EXPLAIN' => $this->language->lang('BUY ME A BEER_EXPLAIN', '<a href="' . $this->language->lang('BUY_ME_A_BEER_URL') . '" target="_blank" rel=”noreferrer noopener”>', '</a>'),
				'S_BUY_ME_A_BEER_TIOI' => true,
			]);
		}
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

			$topic_icons = [];
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
		$topic_icons = $this->get_topic_icons();
		$row = $event['row'];
		$template = $event['forum_row'];
		$forum_icon = [];

		if ($row['enable_icons'] && $row['forum_password_last_post'] === '' && $this->auth->acl_get('f_read', $row['forum_id']))
		{
			if (in_array($row['forum_last_post_id'], array_keys($topic_icons)))
			{
				$icon_id = $topic_icons[$row['forum_last_post_id']];

				$forum_icon = [
					'TOPIC_ICON_IMG' 		=> $this->icons[$icon_id]['img'],
					'TOPIC_ICON_IMG_WIDTH'	=> $this->icons[$icon_id]['width'],
					'TOPIC_ICON_IMG_HEIGHT'	=> $this->icons[$icon_id]['height'],
					'TOPIC_ICON_ALT'		=> !empty($this->icons[$icon_id]['alt']) ? $this->icons[$icon_id]['alt'] : '',
				];
			}
		}

		$event['forum_row'] = array_merge($template, $forum_icon);
	}
}
