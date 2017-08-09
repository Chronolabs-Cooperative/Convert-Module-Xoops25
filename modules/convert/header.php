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
	
	if (!defined(_MD_CONVERT_MODULE_DIRNAME))
		define('_MD_CONVERT_MODULE_DIRNAME', basename(__DIR__));
		
	include_once (dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'mainfile.php');

	ini_set('display_errors', true);
	error_reporting(E_ALL);
	
	set_time_limit(8444);
	
	xoops_loadLanguage('modinfo', _MD_CONVERT_MODULE_DIRNAME);
	xoops_loadLanguage('errors', _MD_CONVERT_MODULE_DIRNAME);
	
	global $convertModule, $convertConfigsList, $convertConfigs, $convertConfigsOptions;

	if (empty($convertModule))
	{
		if (is_a($convertModule = xoops_gethandler('module')->getByDirname(_MD_CONVERT_MODULE_DIRNAME), "XoopsModule"))
		{
			if (empty($convertConfigsList))
			{
				$convertConfigsList = xoops_gethandler('config')->getConfigList($convertModule->getVar('mid'));
				if (!defined('_MD_CONVERT_DEFAULT_TWITTER'))
					define('_MD_CONVERT_DEFAULT_TWITTER',$convertConfigsList['username']);
			}
			if (empty($convertConfigs))
			{
				$convertConfigs = xoops_gethandler('config')->getConfigs(new Criteria('conf_modid', $convertModule->getVar('mid')));
			}
			if (empty($convertConfigsOptions) && !empty($convertConfigs))
			{
				foreach($convertConfigs as $key => $config)
					$convertConfigsOptions[$config->getVar('conf_name')] = $config->getConfOptions();
			}
		}
	}

	include_once (__DIR__ . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'functions.php');

?>