<?php
/**
 * Font Converter for fonts2web.org.uk
*
* You may not change or alter any portion of this comment or credits
* of supporting developers from this source code or any supporting source code
* which is considered copyrighted (c) material of the original comment or credit authors.
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
*
* @copyright   	The XOOPS Project http://fonts2web.org.uk
* @license     	General Public License version 3 (http://labs.coop/briefs/legal/general-public-licence/13,3.html)
* @author      	Simon Roberts (wishcraft) <wishcraft@users.sourceforge.net>
* @subpackage  	convert
* @description 	Converts fonts to web distributional format in a zip pack stamped
* @version		1.0.1
* @link        	http://fonts2web.org.uk
* @link        	http://fonts.labs.coop
* @link			http://internetfounder.wordpress.com
*/

include_once __DIR__ . '/header.php';
xoops_cp_header();

$indexAdmin = new ModuleAdmin();

$fontsHandler = xoops_getModuleHandler('fonts', _MD_CONVERT_MODULE_DIRNAME);

$indexAdmin->addInfoBox(_MD_CONVERT_ADMIN_STATISTICS);
$indexAdmin->addInfoBoxLine(_MD_CONVERT_ADMIN_STATISTICS, "<infolabel>" ._MD_CONVERT_ADMIN_STATS_TOTALUPLOADS. "</infolabel>", $fontsHandler->getTotalUploads(), 'Green');
$indexAdmin->addInfoBoxLine(_MD_CONVERT_ADMIN_STATISTICS, "<infolabel>" ._MD_CONVERT_ADMIN_STATS_TOTALDOWNLOADS. "</infolabel>", $fontsHandler->getTotalDownloads(), 'Green');
$indexAdmin->addInfoBoxLine(_MD_CONVERT_ADMIN_STATISTICS, "<infolabel>" ._MD_CONVERT_ADMIN_STATS_TOTALDOWNLOADSMBS. "</infolabel>", $fontsHandler->getTotalDownloadedMbytes(), 'Blue');
$indexAdmin->addInfoBoxLine(_MD_CONVERT_ADMIN_STATISTICS, "<infolabel>" ._MD_CONVERT_ADMIN_STATS_FILESINCACHE. "</infolabel>", $fontsHandler->getTotalFilesInCache(), 'Red');
$indexAdmin->addInfoBoxLine(_MD_CONVERT_ADMIN_STATISTICS, "<infolabel>" ._MD_CONVERT_ADMIN_STATS_MBSINCACHE. "</infolabel>", $fontsHandler->getTotalMbsInCache(), 'Red');
$indexAdmin->addInfoBoxLine(_MD_CONVERT_ADMIN_STATISTICS, "<infolabel>" ._MD_CONVERT_ADMIN_STATS_FILESINREPO. "</infolabel>", $fontsHandler->getTotalFilesInRepo(), 'Purple');
$indexAdmin->addInfoBoxLine(_MD_CONVERT_ADMIN_STATISTICS, "<infolabel>" ._MD_CONVERT_ADMIN_STATS_MBSINREPO. "</infolabel>", $fontsHandler->getTotalMbsInRepo(), 'Purple');

echo $indexAdmin->addNavigation(basename(__FILE__));
echo $indexAdmin->renderIndex();

include_once __DIR__ . '/footer.php';
//xoops_cp_footer();
