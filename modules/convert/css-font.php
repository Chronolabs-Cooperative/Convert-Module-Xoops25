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
	
	if (!isset($_GET['id']))
	{
		redirect_header(XOOPS_URL . '/modules/convert/index.php', 4, _ERR_CONVERT_GLYPH_NOIDSPECIFIED);
		exit(0);
	}

	if (!isset($_GET['format']) && !in_array($_GET['format'], array('bdf', 'bin', 'cef', 'cff', 'dfont', 'eot', 'fnt', 'gai', 'gsf', 'hqx', 'ik', 'mf', 'otb', 'otf', 'pcf', 'pdb', 'pf3', 'pfa', 'pfb', 'pmf', 'pt3', 'sfd', 'svg', 't42', 'ttc', 'ttf', 'woff', 'fon')))
	{
		redirect_header(XOOPS_URL . '/modules/convert/index.php', 4, _ERR_CONVERT_FONT_NOFORMATSPECIFIED);
		exit(0);
	}
	
	$fontHandler = xoops_getModuleHandler('fonts',_MD_CONVERT_MODULE_DIRNAME);
	
	if (!$font = $fontHandler->getByHash($_GET['id']))
	{
		redirect_header(XOOPS_URL . '/modules/convert/index.php', 4, _ERR_CONVERT_GLYPH_IDNOTFOUND);
		exit(0);
	}
		
	if ($GLOBALS['convertConfigsList']['htaccess']) {
		if (!strpos(sprintf($font->getFontURL('referee'), $_GET['format']), $_SERVER['REQUEST_URI']) && !strpos(sprintf($font->getFontURL('barcode'), $_GET['format']), $_SERVER['REQUEST_URI'])) {
			header('Location: ' . sprintf($font->getFontURL('referee'), $_GET['format']));
			exit(0);
		}
	}
	
	$data = $font->getFontFile($_GET['format']);
	header('Context-type: '. $data['mime']);
	die($data['data']);
	exit(0);
		
?>