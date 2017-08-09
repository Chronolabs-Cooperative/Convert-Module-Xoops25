<?php
/**
 * System Preloads
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright   The XOOPS Project http://sourceforge.net/projects/xoops/
 * @license     GNU GPL 2 (http://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
 * @author      Simon Roberts (AKA +61405130385)
 * @version     $Id: xortify.php 8066 2011-11-06 05:09:33Z beckmi $
 */

defined('XOOPS_ROOT_PATH') or die('Restricted access');


class ConvertTwitterPreload extends XoopsPreloadItem
{

	
	function eventCoreIncludeCommonEnd($args)
	{
		require_once dirname(__DIR__) . '/header.php';
		require_once dirname(__DIR__) . '/class/TwitterAPIExchange.php';
		
		if (strlen($convertConfigsList['token_access']) > 10 && strlen($convertConfigsList['token_secret']) > 10 &&
			strlen($convertConfigsList['consumer_access']) > 10 && strlen($convertConfigsList['consumer_secret']) > 10 )
		{
			$settings = array(
					'consumer_key' => $convertConfigsList['consumer_access'],
					'consumer_secret' => $convertConfigsList['consumer_secret'],
					'oauth_access_token' => $convertConfigsList['token_access'],
					'oauth_access_token_secret' => $convertConfigsList['token_secret']
			);
			
			$criteria = new CriteriaCompo(new Criteria('converted', 'Yes', 'LIKE'));
			$criteria->add(new Criteria('reported', 'Yes', 'LIKE'), 'AND');
			$criteria->add(new Criteria('tweeted', 'No', 'LIKE'), 'AND');
			$criteria->add(new Criteria('fontid', '0', '<>'), 'AND');
			$criteria->setSort('`tweeting`, `uploading`');
			$criteria->setOrder('ASC');
			$criteria->setLimit(1);
				
			$uploadsHandler = xoops_getModuleHandler('uploads', _MD_CONVERT_MODULE_DIRNAME);
			$fontsHandler = xoops_getModuleHandler('fonts', _MD_CONVERT_MODULE_DIRNAME);
				
			if ($uploadsHandler->getCount($criteria)>0)
			{
				foreach($uploadsHandler->getObjects($criteria) as $upload)
				{
					$font = $fontsHandler->get($upload->getVar('fontid'));
					$url = "https://api.twitter.com/1.1/statuses/update.json";
					$tweettxt = sprintf(_MD_CONVERT_RELEASE_TWEET, $font->getVar('name'), number_format($font->getVar('open-bytes') / 1024 / 1024, 2), $font->getFontDisplayURL(), $upload->getVar('twitter'));
					$requestMethod = 'GET';
					$getfields = array('status' => $tweettxt);
					$postfields = array();
					$twitter = new TwitterAPIExchange($settings);
					$txt = json_decode($twitter->setGetfield($getfield)->buildOauth($url, $requestMethod)->setPostfields($postfields)->performRequest(), true);
					if(!isset($txt['errors']) && count($txt['errors']) == 0)
					{
						$upload->setVar('tweeted', 'Yes');
						$upload->setVar('tweeting', microtime(true));
						$uploadsHandler->insert($upload, true);
					} else {
						$upload->setVar('tweeting', microtime(true));
						$uploadsHandler->insert($upload, true);
					}
				}
			}
		}
	}
}
?>
