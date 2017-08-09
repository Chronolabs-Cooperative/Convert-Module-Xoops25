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


// Font Upload Error
define('_ERR_CONVERT_UPLOAD_NOFILES','No files specified for uploading to the system!');
define('_ERR_CONVERT_UPLOAD_UNKNOWNEXTENSION','The file extension type of <strong>%s</strong> is not valid you can only upload the following file types: <em>%s</em>!');
define('_ERR_CONVERT_UPLOAD_NOTWITTERUSER','You have not specified a twitter username, the default is: <strong>%s</strong>');
define('_ERR_CONVERT_UPLOAD_EMAILINVALID','The email you have given which is: %s - <strong>Is Invalid, not an email address allowed!</strong>');
define('_ERR_CONVERT_UPLOAD_NOEMAILGIVEN','There has been no email given!');
define('_ERR_CONVERT_UPLOAD_NONAMEGIVEN','There has been no name for the font copyrightin given!');
define('_ERR_CONVERT_UPLOAD_NOBIZOGIVEN','There has been no business or organisation for the font copyrighting given!');
define('_ERR_CONVERT_UPLOAD_NOMAKEFOLDER','I have been unable to make the uploading working path for the file at: <em>%s</em>');
define('_ERR_CONVERT_UPLOAD_NOFILEMOVE','<h1 style=\'color:rgb(198,0,0);\'>Uploading Error Has Occured</h1><br/><p>Fonts API was unable to recieve and store: <strong>%s</strong>!</h1>');
define('_ERR_CONVERT_UPLOAD_COMPLETE','Completed Uploading File and Converting and Downloading the font(s)!');
define('_ERR_CONVERT_UPLOAD_SECFAILED', 'Form Security Token Failed, upload cannot continue!');
define('_ERR_CONVERT_UPLOAD_LICENCINGERROR','The file you have uploaded, is locked and unmodifiable and strippable to your own licensed font file, I am sorry we will be unable to convert this file!');

// Fonts Class Error Messages
define('_ERR_CONVERT_FONTS_REPORESNOTFOUND','Repository Font File is Missing!');
define('_ERR_CONVERT_FONTS_CACHEFILEMISSING','Cache File is missing from the repository of Cache Font Files!');

// Naming Cue Error Messages
define('_ERR_CONVERT_NAMING_NOIDSPECIFIED','No Identify Hash Specified on the $_GET["id"] input!');
define('_ERR_CONVERT_NAMING_IDNOTFOUND','The Identify Hash Specified on the $_GET["id"] input; could not be found in the database at any congruent basis!');

// Glyph Preview Error Messages
define('_ERR_CONVERT_GLYPH_NOIDSPECIFIED','No Identify Hash Specified on the $_GET["id"] input!');
define('_ERR_CONVERT_GLYPH_IDNOTFOUND','The Identify Hash Specified on the $_GET["id"] input; could not be found in the database at any congruent basis!');
define('_ERR_CONVERT_GLYPH_NOCHARSPECIFIED','No Character UTF Number specified in $_GET["char"]!');

// Preview Error Messages
define('_ERR_CONVERT_PREVIEW_NOIDSPECIFIED','No Identify Hash Specified on the $_GET["id"] input!');
define('_ERR_CONVERT_PREVIEW_IDNOTFOUND','The Identify Hash Specified on the $_GET["id"] input; could not be found in the database at any congruent basis!');

// Preview Error Messages
define('_ERR_CONVERT_HISTORY_NOIDSPECIFIED','No Identify Hash Specified on the $_GET["id"] input!');
define('_ERR_CONVERT_HISTORY_IDNOTFOUND','The Identify Hash Specified on the $_GET["id"] input; could not be found in the database at any congruent basis!');
define('_ERR_CONVERT_HISTORY_NOFONTS','There are no fonts in the history currently, you cannot browse this list for the moment!');

// Download Error Messages
define('_ERR_CONVERT_DOWNLOAD_NOIDSPECIFIED','No Identify Hash Specified on the $_GET["id"] input!');
define('_ERR_CONVERT_DOWNLOAD_IDNOTFOUND','The Identify Hash Specified on the $_GET["id"] input; could not be found in the database at any congruent basis!');

// Font Error Messages
define('_ERR_CONVERT_FONT_NOIDSPECIFIED','No Identify Hash Specified on the $_GET["id"] input!');
define('_ERR_CONVERT_FONT_IDNOTFOUND','The Identify Hash Specified on the $_GET["id"] input; could not be found in the database at any congruent basis!');

?>