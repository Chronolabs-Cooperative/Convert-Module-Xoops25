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

	if (!isset($_GET['char']))
	{
		redirect_header(XOOPS_URL . '/modules/convert/index.php', 4, _ERR_CONVERT_GLYPH_NOCHARSPECIFIED);
		exit(0);
	}
	
	$fontHandler = xoops_getModuleHandler('fonts',_MD_CONVERT_MODULE_DIRNAME);
	
	if (!$font = $fontHandler->getByHash($_GET['id']))
	{
		redirect_header(XOOPS_URL . '/modules/convert/index.php', 4, _ERR_CONVERT_GLYPH_IDNOTFOUND);
		exit(0);
	}
		
	if ($GLOBALS['convertConfigsList']['htaccess']) {
		if (!strpos(sprintf($font->getGlyphsURL('referee'), $_GET['char']), $_SERVER['REQUEST_URI']) && !strpos(sprintf($font->getGlyphsURL('barcode'), $_GET['char']), $_SERVER['REQUEST_URI'])) {
			header('Location: ' . sprintf($font->getGlyphsURL('referee'), $_GET['char']));
			exit(0);
		}
	}
	
	xoops_load("XoopsCache");
	if (!$image = XoopsCache::read(md5(__FILE__.$_GET['id'].$_GET['char'])))
	{
		sleep(mt_rand(1,9));
		$image = array('data' => $font->getGlyphPreview($_GET['char']));
		XoopsCache::write(md5(__FILE__.$_GET['id'].$_GET['char']), $image, 8444);
	} elseif (isset($image['data']) && !empty($image['data'])) {
		XoopsCache::write(md5(__FILE__.$_GET['id'].$_GET['char']), $image, 8444);
	} else {
		sleep(mt_rand(1,9));
		$image = array('data' => $font->getGlyphPreview($_GET['char']));
		XoopsCache::write(md5(__FILE__.$_GET['id'].$_GET['char']), $image, 8444);
	}
	header('Context-type: image/png');
	die($image['data']);
	exit(0);
		
?>