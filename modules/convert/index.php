<?php
/**
 * Please Email Ticketer of Batch Group & User Emails
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright   	The XOOPS Project http://sourceforge.net/projects/xoops/
 * @license     	General Public License version 3 (http://labs.coop/briefs/legal/general-public-licence/13,3.html)
 * @author      	Simon Roberts (wishcraft) <wishcraft@users.sourceforge.net>
 * @subpackage  	please
 * @description 	Email Ticking for Support/Faults/Management of Batch Group & User managed emails tickets
 * @version			1.0.5
 * @link        	https://sourceforge.net/projects/chronolabs/files/XOOPS%202.5/Modules/please
 * @link        	https://sourceforge.net/projects/chronolabs/files/XOOPS%202.6/Modules/please
 * @link			https://sourceforge.net/p/xoops/svn/HEAD/tree/XoopsModules/please
 * @link			http://internetfounder.wordpress.com
 */

	require_once (__DIR__ . DIRECTORY_SEPARATOR . 'header.php');
	
	if ($GLOBALS['convertConfigsList']['htaccess']) {
		$url = XOOPS_URL . '/' . $GLOBALS['convertConfigsList']['base'] . '/index.html';
		if (!strpos($url, $_SERVER['REQUEST_URI'])) {
			header('Location: ' . $url);
			exit(0);
		}
	}
	
	xoops_load('XoopsCache');
	
	require_once(__DIR__ . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'functions.php');	
	$xoopsOption['template_main'] = 'convert_index.html';
	include $GLOBALS['xoops']->path('/header.php');	
	$GLOBALS['xoTheme']->addStylesheet(XOOPS_URL . "/modules/' . _MD_CONVERT_MODULE_DIRNAME . '/language/" . $GLOBALS['xoopsConfig']['language'] . "/style.css");
	$GLOBALS['xoopsTpl']->assign('uploadform', getHTMLForm('uploads'));
	$GLOBALS['xoopsTpl']->assign('history', xoops_getmodulehandler('fonts', basename(__DIR__))->getRecentDivs());
	include $GLOBALS['xoops']->path('/footer.php');		
	exit(0);
		
?>