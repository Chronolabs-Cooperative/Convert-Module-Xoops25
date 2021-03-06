<?php
/**
 * Chronolabs Fonting Repository Services REST API API
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Chronolabs Cooperative http://labs.coop
 * @license         General Public License version 3 (http://labs.coop/briefs/legal/general-public-licence/13,3.html)
 * @package         fonts
 * @since           2.1.9
 * @author          Simon Roberts <wishcraft@users.sourceforge.net>
 * @subpackage		api
 * @description		Fonting Repository Services REST API
 * @link			http://sourceforge.net/projects/chronolabsapis
 * @link			http://cipher.labs.coop
 */

	require_once (__DIR__ . DIRECTORY_SEPARATOR . 'header.php');
	
	if (!isset($_GET['id']))
	{
		redirect_header(XOOPS_URL . '/modules/convert/index.php', 4, _ERR_CONVERT_PREVIEW_NOIDSPECIFIED);
		exit(0);
	}
	
	$fontHandler = xoops_getModuleHandler('fonts',_MD_CONVERT_MODULE_DIRNAME);
	
	if (!$font = $fontHandler->getByHash($_GET['id']))
	{
		redirect_header(XOOPS_URL . '/modules/convert/index.php', 4, _ERR_CONVERT_PREVIEW_IDNOTFOUND);
		exit(0);
	}
	
	$action = in_array($_REQUEST['action'], array('ignored', 'caller', 'download', 'repository', 'release', 'convert', 'storage', 'sorting', 'uploaded', 'allocated', 'expired', 'completed'))
				?$_REQUEST['action']
				:die("Unknown Action Specified");
	
	switch($action)
	{
		/**
		 * When an font already exist and need not be converted or surveyed this callback occures
		 * 
		 * Variables:
		 * 
		 *     (boolean) 	$_POST['allocated'] = The Actual Font exists already and allocated
		 *     (string) 	$_POST['name'] = The entity submitting name
		 *     (string) 	$_POST['email'] = The entity submitting email
		 *     (string) 	$_POST['bizo'] = The entity submitting business name
		 *     (string) 	$_POST['fingerprint'] = Md5 checksum for the filename on callback
		 *     (string) 	$_POST['filename'] = The internally set filename that has been ignored
		 *     (boolean) 	$_POST['culled'] = The flag set to true with culled file
		 */
		case 'ignored':
			
			break;
			
			
		/**
		 * When an font resource is called via file retrival this callback occures
		 * 
		 * Variables:
		 * 
		 *     (string) 	$_POST['fingerprint'] = The Actual Font fingerprint
		 *     (array) 		$_POST['ipid'] = The IP Addy and location of the caller
		 *     (float) 		$_POST['when'] = microtime(true) - UNIX_TIMESTAMP of when called
		 *     (string) 	$_POST['type'] = The font file type called
		 *     (string) 	$_POST['referee'] = The referee_uri of the caller
		 *     (array) 		$_POST['names'] = Font names for the font
		 *     (array) 		$_POST['nodes'] = Font nodes for the font
		 */
		case 'caller':
			
			break;

			
		/**
		 * When an font resource is called via file retrival this callback occures
		 * 
		 * Variables:
		 * 
		 *     (string) 	$_POST['fingerprint'] = The Actual Font fingerprint
		 *     (array) 		$_POST['ipid'] = The IP Addy and location of the caller
		 *     (float) 		$_POST['when'] = microtime(true) - UNIX_TIMESTAMP of when called
		 *     (string) 	$_POST['type'] = The font file archive type download
		 *     (string) 	$_POST['referee'] = The referee_uri of the caller
		 *     (array) 		$_POST['names'] = Font names for the font
		 *     (array) 		$_POST['nodes'] = Font nodes for the font
		 */
		case 'download':
			
			break;


		/**
		 * When an font is released on the API this callback is called!
		 * 
		 * Variables:
		 * 
		 *     (string) 	$_POST['fingerprint'] = The Actual Font Resource Hash-info item key
		 *     (string) 	$_POST['name'] = The Actual Font name for your region
		 *     (array) 		$_POST['files'] = The Filename's associated with upload hashinfo key
         *     (string) 	$_POST['archive-file'] = The Archive the fonts in this key is called
		 *     (string) 	$_POST['archive-md5'] = Md5 checksum for the filename on callback
		 *     (array) 		$_POST['contributors'] = The array of contributors to this font
		 *     (array) 		$_POST['nodes'] = The typographic nodes associated with font
		 *     (array) 		$_POST['meta'] = The meta-informaion for font
		 *     (array) 		$_POST['file-hashes'] = The files in the archives md5 fingerprints
		 *     (string) 	$_POST['download-url'] = The font archive download URI
		 *     (integer) 	$_POST['bytes'] = The font archives physical size in bytes
		 */
		case 'release':
			$criteria = new Criteria('name', $font->getVar('name'), 'LIKE');
			foreach($fontHandler->getObjects($criteria) as $fontier)
			{
				$fontier->setVar('identity', $_POST['fingerprint']);
				$fontier->setVar('sydnication', time());
				$fontHandler->insert($fontier, true);
			}
				
			break;

		

		/**
		 * When an item is add to the font resources storage file places
		 * 
		 * Variables:
		 * 
		 *     (string) 	$_POST['fingerprint'] = The Actual Font Resource Hash-info item key
		 *     (array) 		$_POST['files'] = The Filename's associated with upload hashinfo key
         *     (string) 	$_POST['archive-file'] = The Archive the fonts in this key is called
		 *     (string) 	$_POST['archive-md5'] = Md5 checksum for the filename on callback
		 */
		case 'storage':
			
			break;


		/**
		 * When an upload is being ordered and sorted is complete
		 * 
		 * Variables:
		 * 
		 *     (string) 	$_POST['fingerprint'] = Md5 checksum for the filename on callback
		 *     (string) 	$_POST['fingerprint'] = Forensic Temporary Hash-info item key
		 *     (string) 	$_POST['files'] = The Filename's associated with upload hashinfo key
		 */
		case 'sorting':
			
			break;



		/**
		 * When an upload is recived immediately called on upload
		 * 
		 * Variables:
		 * 
		 *     (integer) 	$_POST['allocated'] = Maximum of allocated font categorisation survey/questionairs
		 *     (string) 	$_POST['fingerprint'] = Md5 checksum for the filename on callback
		 *     (string) 	$_POST['fingerprint'] = Forensic Temporary Hash-info item key
		 *     (string) 	$_POST['email'] = Converters Email Address Specified
		 *     (string) 	$_POST['name'] = Converters Name Specified
		 *     (string) 	$_POST['bizo'] = = Converters Organisational Name Specified
		 *     (integer) 	$_POST['frequency'] = Number of Seconds to allocate to the frequency for example emails
		 *     (integer) 	$_POST['elapsing'] = Maximum of elapsing seconds for processes shunting
		 *     (boolean) 	$_POST['culled'] = Any same enaming of fonts in upload that was culled from conversion
		 *     (string) 	$_POST['filename'] = The Filename being Specified for the file
		 *     (array) 		$_POST['ipid'] = The IP Addy and location of the caller
		 */
		case 'uploaded':
			
			break;


		/**
		 * When an survey is allocated this is called to the callback api of your own
		 * 
		 * Variables:
		 * 
		 *     (string) 	$_POST['fingerprint'] = Font Fingerprint Checksum Hash-info item key
		 *     (string) 	$_POST['when'] = microtime(true) ~ When commit to repository
		 *     (string) 	$_POST['uri'] = Link to the file on the repository
		 *     (array) 		$_POST['names'] = Font names for the font
		 *     (array) 		$_POST['nodes'] = Font nodes for the font
		 */
		case 'repository':
			
			break;


		/**
		 * When an survey is allocated this is called to the callback api of your own
		 * 
		 * Variables:
		 * 
		 *     (string) 	$_POST['fingerprint'] = Font Fingerprint Checksum Hash-info item key
		 *     (string) 	$_POST['email'] = Questionair for survey Email Address Specified
		 *     (string) 	$_POST['name'] = Questionair for survey Name Specified
		 *     (integer) 	$_POST['finish'] = UNIX_TIMESTAMP() for when Conversion Finished
		 *     (array) 		$_POST['files'] = File generated
		 */
		case 'convert':
			
			break;


		/**
		 * When an survey is allocated this is called to the callback api of your own
		 * 
		 * Variables:
		 * 
		 *     (string) 	$_POST['key'] = Survey Checksum Hash-info item key
		 *     (string) 	$_POST['fingerprint'] = Forensic Temporary Hash-info font file item key
		 *     (string) 	$_POST['email'] = Questionair for survey Email Address Specified]
		 *     (integer) 	$_POST['expires'] = UNIX_TIMESTAMP() for when Questionair expires
		 *     (string) 	$_POST['scope'] = Scope of the survey
		 *     (integer) 	$_POST['completed'] = Number of survey this individual has completed
		 */
		case 'allocated':
			
			break;


		/**
		 * When an survey has expired this is called to the callback api of your own
		 * 
		 * Variables:
		 * 
		 *     (string) 	$_POST['key'] = Survey Checksum Hash-info item key
		 *     (string) 	$_POST['fingerprint'] = Forensic Temporary Hash-info font file item key
		 *     (string) 	$_POST['email'] = Questionair for survey Email Address Specified
		 *     (string) 	$_POST['name'] = Questionair for survey Name Specified
		 *     (integer) 	$_POST['expired'] = UNIX_TIMESTAMP() for when Questionair expired
		 */
		case 'expired':
			
			break;
		

		/**
		 * When an survey ha been comlpeted this is called to the callback api of your own
		 * 
		 * Variables:
		 * 
		 *     (string) 	$_POST['key'] = Survey Checksum Hash-info item key
		 *     (string) 	$_POST['fingerprint'] = Forensic Temporary Hash-info font file item key
		 *     (string) 	$_POST['email'] = Questionair for survey Email Address Specified
		 *     (string) 	$_POST['name'] = Questionair for survey Name Specified
		 *     (integer) 	$_POST['expired'] = UNIX_TIMESTAMP() for when Questionair expired
		 *     (array) 		$_POST['data'] = Elemented array with Questionair answers
		 */
		case 'completed':
			
			break;
		

		/**
		 * When an survee removes themselves for no more mail ever callback api
		 * 
		 * Variables:
		 * 
		 *     (string) 	$_POST['key'] = Survey Checksum Hash-info item key
		 *     (string) 	$_POST['fingerprint'] = Forensic Temporary Hash-info font file item key
		 *     (string) 	$_POST['email'] = Questionair for survey Email Address Specified
		 *     (string) 	$_POST['name'] = Questionair for survey Name Specified
		 *     (integer) 	$_POST['when'] = UNIX_TIMESTAMP() for when all future Questionair stopped fo entity
		 *     (array) 		$_POST['closed'] = List of all survey hash-info keys for affected Questionair's
		 *     (array) 		$_POST['ipid'] = The IP Addy and location of the caller
		 */
		case 'optout':
			
			break;
	}
?>