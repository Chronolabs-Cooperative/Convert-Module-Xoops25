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

define('_PL_CONVERT_NEWS_DIRNAME','xnews');
define('_PL_CONVERT_NEWS_TOPICID',1);

class ConvertComplexityPreload extends XoopsPreloadItem
{

	
	function eventCoreIncludeCommonEnd($args)
	{
		xoops_load("XoopsLists");
		require_once dirname(__DIR__) . '/' . 'header.php';
		
		$images = XoopsLists::getImgListAsArray($dir = XOOPS_ROOT_PATH . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . 'complexity' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'backgrounds');
		if (count($images) > $convertConfigsList['images'])
		{
			while(count($images) > mt_rand(5, $convertConfigsList['maximum']))
			{
				shuffle($images);
				unset($images[0]);
			}
			foreach($images as $image)
				unlink($image);
		}
		
	}
}
?>
