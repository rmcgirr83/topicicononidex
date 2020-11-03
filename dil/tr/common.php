<?php
/**
*
* @package language Topic icon on index
* @copyright (c) 2020 RMcGirr83
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
* Translated into Turkish: O Belde (forum.obelde.com) - HE
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = [];
}

$lang = array_merge($lang, [
	//Donation
	'TRANSLATION_INFO'	=> '<br />Tercüme: <a href="https://obelde.com/">O Belde</a> <a href="https://forum.obelde.com/">Forum</a>',
	'PAYPAL_IMAGE_URL'          => 'https://www.paypalobjects.com/webstatic/en_US/i/btn/png/silver-pill-paypal-26px.png',
	'PAYPAL_ALT'                => 'PayPal bağışı yapın',
	'BUY_ME_A_BEER_URL'         => 'https://paypal.me/RMcGirr83',
	'BUY_ME_A_BEER'				=> 'Bu eklenti için bana bir döner-ayran alabilirsin.',
	'BUY_ME_A_BEER_SHORT'		=> 'Bu eklenti için geliştiricisine bağış yapın.',
	'BUY_ME_A_BEER_EXPLAIN'		=> 'Bu eklenti tamamen ücretsizdir. phpBB forumlarını daha güzel ve vasıflı bir yer hale getirmek için zaman harcadığım bir projedir. Bu eklentiyi sevdiyseniz veya forumunuza fayda sağladıysa, lütfen <a href="https://paypal.me/RMcGirr83" target="_blank" rel=”noreferrer noopener”> bana döner-ayran alın</ a>, buna çok sevinirim <i class="fa fa-smile-o" style="color:green;font-size:1.5em;" aria-hidden="true"></i>',
]);
