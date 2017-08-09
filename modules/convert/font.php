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
		redirect_header(XOOPS_URL . '/modules/convert/index.php', 4, _ERR_CONVERT_FONT_NOIDSPECIFIED);
		exit(0);
	}
	
	$fontHandler = xoops_getModuleHandler('fonts',_MD_CONVERT_MODULE_DIRNAME);
	$uploadsHandler = xoops_getModuleHandler('uploads',_MD_CONVERT_MODULE_DIRNAME);
	$filesHandler = xoops_getModuleHandler('files',_MD_CONVERT_MODULE_DIRNAME);
	$glyphsHandler = xoops_getModuleHandler('glyphs',_MD_CONVERT_MODULE_DIRNAME);
	
	if (!$font = $fontHandler->getByHash($_GET['id']))
	{
		redirect_header(XOOPS_URL . '/modules/convert/index.php', 4, _ERR_CONVERT_FONT_IDNOTFOUND);
		exit(0);
	}
	
	if ($GLOBALS['convertConfigsList']['htaccess']) {
		if (!strpos($font->getFontDisplayURL(), $_SERVER['REQUEST_URI'])) {
			header('Location: ' . $font->getFontDisplayURL());
			exit(0);
		}
	}
	
	xoops_load('XoopsCache');
	
	require_once(__DIR__ . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'functions.php');	
	$xoopsOption['template_main'] = 'convert_fonts.html';
	include $GLOBALS['xoops']->path('/header.php');		
	$GLOBALS['xoTheme']->addScript(XOOPS_URL . "/browse.php?Frameworks/jquery/jquery.js");
	$GLOBALS['xoTheme']->addScript(XOOPS_URL . "/browse.php?Frameworks/jquery/jquery.ui.js");
	$GLOBALS['xoTheme']->addScript("", array(), "  $( function() {
    $(\"#tabs\" ).tabs();
  } );");
	$GLOBALS['xoTheme']->addStylesheet(XOOPS_URL . "/modules/' . basename(__DIR__) . '/language/" . $GLOBALS['xoopsConfig']['language'] . "/style.css");
	$GLOBALS['xoTheme']->addStylesheet($font->getCSSURL());
	$upload = $uploadsHandler->get($font->getVar('uploadid'));
	
	xoops_load("XoopsCache");
	if (!$cssdata = XoopsCache::read($cache = "convert_css_".md5($font->getVar('referee'))))
	{
		$cssdata = array();
		$cssdata['url'] = $font->getCSSURL();
		$cssdata['code'] = getURIData($cssdata['url'], 65, 65);
		XoopsCache::write($cache, $cssdata, 3600 * 24 * 7 * 4 * 48);
	}
	$GLOBALS['xoopsTpl']->assign('cssdata', $cssdata,0);
	$GLOBALS['xoopsTpl']->assign('xoops_meta_description', $keywords = 'Font: '.$font->getVar('name') . ' ~ Licensing Name: ' ._MD_CONVERT_LICENSE_NAME. ' ~ Licensing Code: ' . _MD_CONVERT_LICENSE_CODE . ' ~ Licensed Name: ' . $upload->getVar('name') . ' ~ Licensed Organisation: ' . $upload->getVar('company') . ' ~ Download Size: '.number_format($font->getvar('zip-bytes'),0).' bytes ~ Number of Files: '.number_format($font->getvar('zip-files'),0));
	$GLOBALS['xoopsTpl']->assign('xoops_meta_keywords', implode(',',explode('-',sef($keywords))));
	$GLOBALS['xoopsTpl']->assign('xoops_pagetitle', 'Font: ' . $font->getVar('name') . ' by ' . $upload->getVar('name') . ' ('.$upload->getVar('company').')');
	$GLOBALS['xoopsTpl']->assign('font', $font->getValues(array_keys($font->vars)));
	$GLOBALS['xoopsTpl']->assign('preview', $font->getPreviewURL());
	$GLOBALS['xoopsTpl']->assign('download', $font->getDownloadURL());
	$GLOBALS['xoopsTpl']->assign('upload', $upload->getValues(array_keys($upload->vars)));
	$GLOBALS['xoopsTpl']->assign('licensetxt', array('license'=>str_replace("\n", "<br />", file_get_contents(__DIR__ . '/include/data/LICENSE')), 'academic' => str_replace("\n", "<br />", file_get_contents(__DIR__ . '/include/data/ACADEMIC'))));
	$GLOBALS['xoopsTpl']->assign('license', _MD_CONVERT_LICENSE_NAME);
	$GLOBALS['xoopsTpl']->assign('licensecode', _MD_CONVERT_LICENSE_CODE . ' + ACADEMIC');
	$GLOBALS['xoopsTpl']->assign('licenseurl', _MD_CONVERT_LICENSE_URL);
	$GLOBALS['xoopsTpl']->assign('pack', $font->getvar('pack'));
	$GLOBALS['xoopsTpl']->assign('zipbytes', number_format($font->getvar('zip-bytes'),0));
	$GLOBALS['xoopsTpl']->assign('zipfiles', number_format($font->getvar('zip-files'),0));
	
	$criteria = new Criteria('fontid', $font->getVar('id'));
	$criteria->setSort('`path`');
	$criteria->setOrder('ASC');
	foreach($filesHandler->getObjects($criteria) as $file)
		$GLOBALS['xoopsTpl']->append('files', $file->getValues(array_keys($file->var)));
	
	$criteria = new Criteria('fontid', $font->getVar('id'));
	$criteria->setSort('`value`');
	$criteria->setOrder('ASC');
	foreach($glyphsHandler->getObjects($criteria) as $glyph)
		if ($glyph->getVar('value') > 31 && $glyph->getVar('value') < 124)
			if (mt_rand(0,3)==2 || mt_rand(0,6)==5)
				$GLOBALS['xoopsTpl']->append('glyphs', sprintf($font->getGlyphsURL(), $glyph->getVar('value')));
	
	include $GLOBALS['xoops']->path('/footer.php');		
	exit(0);
		
?>