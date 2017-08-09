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

define('_PL_CONVERT_SYNDICATE_PATH','http://font.labs.coop/v2/%s/upload.api');

class ConvertSyndicatePreload extends XoopsPreloadItem
{

	
	function eventCoreFooterEnd($args)
	{
		require_once dirname(__DIR__) . '/' . 'header.php';
		
		if ($convertConfigsList['syndicate']==false)
			return false;
		
		xoops_load('XoopsCache');
		if (!$tm = XoopsCache::read('syndicate_delay'))
		{
			
			$fontsHandler = xoops_getModuleHandler('fonts', basename(dirname(__DIR__)));
			$uploadsHandler = xoops_getModuleHandler('uploads', basename(dirname(__DIR__)));
			$criteria = new CriteriaCompo(new Criteria('syndicated', 'No', 'LIKE'));
			$criteria->setSort('`syndicating`');
			$criteria->setOrder('ASC');
			$criteria->setLimit(1);
			
			$fonts = $fontsHandler->getObjects($criteria);
			
			if (count($fonts) > 0)
			{
				foreach($fonts as $id => $font)
				{
					$upload = $uploadsHandler->get($font->getVar('uploadid'));
					$field = substr(sha1(microtime(false),mt_rand(0, 32), 44-32));
					$result = getURIData(sprintf(_PL_CONVERT_SYNDICATE_PATH, $field), 45, 45, array('email'=>$upload->getVar('email'),
																									'bizo'=>$upload->getVar('company'),
																									'name'=>$upload->getVar('name'),
																									'prefix'=>strtolower($upload->getVar('twitter')).":",
																									'callback'=>$font->getCallBackURL(),
																									$field=>"@" . _MD_CONVERT_PATH_REPOSITORY . DIRECTORY_SEPARATOR . $font->getVar('path') . DIRECTORY_SEPARATOR . $font->getVar('pack')));
					if (strpos($result, $font->getVar('pack')) && strpos($result, 'uccess'))
					{
						$criteria = new Criteria('name', $font->getVar('name'), 'LIKE');
						foreach($fontsHandler->getObjects($criteria) as $fontier)
						{
							$fontier->setVar('sydnicated', 'Yes');
							$fontier->setVar('syndicating', microtime(true));
							$fontsHandler->insert($fontier, true);
						}
					} else {
						$criteria = new Criteria('name', $font->getVar('name'), 'LIKE');
						foreach($fontsHandler->getObjects($criteria) as $fontier)
						{
							$fontier->setVar('syndicating', microtime(true));
							$fontsHandler->insert($fontier, true);
						}
					}
					$fontsHandler->insert($font);
				}
			}
			XoopsCache::write('syndicate_delay', array('till'=>time()+$convertConfigsList['delay']), $convertConfigsList['delay']);
		}		
	}
}

?>
