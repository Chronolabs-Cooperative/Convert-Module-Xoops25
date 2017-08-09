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

set_time_limit(60);

define('_PL_CONVERT_NEWS_DIRNAME','xnews');
define('_PL_CONVERT_NEWS_TOPICID',1);

class ConvertCachePreload extends XoopsPreloadItem
{

	
	function eventCoreFooterEnd($args)
	{
		require_once dirname(__DIR__) . '/' . 'header.php';
		$fontsHandler = xoops_getModuleHandler('fonts', basename(dirname(__DIR__)));
		$fonts = $fontsHandler->getExpiredCacheObjects($convertConfigsList['seconds']);
		if (count($fonts) > 0)
		{
			$GLOBALS['xoopsDB']->queryF('START TRANSACTION');
			foreach($fonts as $id => $font)
			{
				if (file_exists($file = _MD_CONVERT_PATH_CACHE . DIRECTORY_SEPARATOR . $font->getVar('cachefile')))
				{
					unlink($file);
					$font->setVar('deleted', microtime(true));
					$fontsHandler->insert($font, true);
				}
			}
			$GLOBALS['xoopsDB']->queryF('COMMIT');
		}
		
	}
}

?>
