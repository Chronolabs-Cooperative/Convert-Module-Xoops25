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
 * @license         GNU GPL 2 (http://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
 * @package         fonts
 * @since           2.1.9
 * @author          Simon Roberts <wishcraft@users.sourceforge.net>
 * @subpackage		api
 * @description		Fonting Repository Services REST API
 * @link			http://sourceforge.net/projects/chronolabsapis
 * @link			http://cipher.labs.coop
 */

	
	use FontLib\Font;
	require_once __DIR__ . '/class/FontLib/Autoloader.php';
	
	
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'header.php';
	
	xoops_load('XoopsSecurity');
	xoops_loadLanguage('errors', _MD_CONVERT_MODULE_DIRNAME);
	xoops_loadLanguage('modinfo', _MD_CONVERT_MODULE_DIRNAME);
	
	$uploadsHandler = xoops_getModuleHandler('uploads', _MD_CONVERT_MODULE_DIRNAME);
	$fontsHandler = xoops_getModuleHandler('fonts', _MD_CONVERT_MODULE_DIRNAME);
	$filesHandler = xoops_getModuleHandler('files', _MD_CONVERT_MODULE_DIRNAME);
	$glyphsHandler = xoops_getModuleHandler('glyphs', _MD_CONVERT_MODULE_DIRNAME);
	
	set_time_limit(3600*36*9*14*28);
	$time = time();
	$filetypes = $error = array();
	
	$security = new XoopsSecurity();
	if (!$security->check(true, $_REQUEST['XOOPS_TOKEN'], 'XOOPS_TOKEN'))
		$errors[] = _ERR_CONVERT_UPLOAD_SECFAILED;
		
	if (!isset($_FILES) || empty($_FILES))
	{
		$error[] = _ERR_CONVERT_UPLOAD_NOFILES;
	} else { 
		$pass = false;
		$fonttitle = array();
		foreach($_FILES as $key => $files)
		{
			$pass=false;
			if (!empty($_FILES[$key]['tmp_name']) && isset($_FILES[$key]['tmp_name']))
				foreach(getFontExtensions() as $xtension)
				{
					if (strtolower(substr($_FILES[$key]['name'], strlen($_FILES[$key]['name'])- strlen($xtension))) == strtolower($xtension))
						if (in_array($xtension, getFontExtensions()))
						{
							$filetypes[$key] = $xtension;
							$fonttitle[$key] = spacerName(str_replace('.'.$xtension, '', $_FILES[$key]['name']));
							$pass=true;
							continue;
						}
				}
			else 
				$pass=true;
			if ($pass == false)
				$error[] = sprintf(_ERR_CONVERT_UPLOAD_UNKNOWNEXTENSION, $_FILES[$key]['name'], implode("</em>&nbsp;<em>*.", $extensions));
		}
	}
	
	$purl = parse_url("http://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"]);
	parse_str($purl['query'], $parse);
	foreach($_REQUEST as $key => $values)
		$parse[$key] = $values;
	
	$parse['twitter'] = str_replace(array('http', 'https', '://', '/', 'twitter.com'), '', strtolower($parse['twitter']));	
	if (!isset($parse['twitter']) || empty($parse['twitter']) || strlen(trim($parse['twitter']))==0) {
		$error[] = sprintf(_ERR_CONVERT_UPLOAD_NOTWITTERUSER, '@'._MD_CONVERT_DEFAULT_TWITTER);
	}
	
	$email = false;
	if (isset($parse['email']) || !empty($parse['email'])) {
		if (!checkEmail($parse['email']))
			$error[] = sprintf(_ERR_CONVERT_UPLOAD_EMAILINVALID, $parse['email']);
		else 
			$email = true;
	} else
		$error[] = _ERR_CONVERT_UPLOAD_NOEMAILGIVEN;
		
	if (!isset($parse['name']) || empty($parse['name'])) 
		$error[] = _ERR_CONVERT_UPLOAD_NONAMEGIVEN;
		
	if (!isset($parse['bizo']) || empty($parse['bizo']))
		$error[] = _ERR_CONVERT_UPLOAD_NOBIZOGIVEN;
	
	$keys = array();
	$uploadpaths = array();
	if ($email == true)
		foreach($_FILES as $key => $files)
		{
			if (!empty($_FILES[$key]['tmp_name']) && isset($_FILES[$key]['tmp_name']))
			{
				$uploadpaths[$key] = DIRECTORY_SEPARATOR . $parse['email'] . DIRECTORY_SEPARATOR . microtime(true) . DIRECTORY_SEPARATOR . md5($key.$_FILES[$key]['name'].microtime(true));
				if (!is_dir(constant("_MD_CONVERT_PATH_UPLOADS") . $uploadpaths[$key])) {
					if (!mkdir(constant("_MD_CONVERT_PATH_UPLOADS") . $uploadpaths[$key], 0777, true)) {
						$error[] = sprintf(_ERR_CONVERT_UPLOAD_NOMAKEFOLDER, constant("_MD_CONVERT_PATH_UPLOADS") . $uploadpaths[$key]);
					}
				}
			}
		}
	
	if (count($error)>0)
	{
		redirect_header(XOOPS_URL . '/modules/' . _MD_CONVERT_MODULE_DIRNAME . '/index.php', 7, implode("<br />", $error));
		exit(0);
	}
	$GLOBALS['xoopsDB']->queryF('START TRANSACTION');
	$fonts = $files = $glyphs = $eotvalues = $eotfile = $uploads = $files = $successes = array();
	foreach($_FILES as $key => $files)
	{
		
		if (!empty($_FILES[$key]['tmp_name']) && isset($_FILES[$key]['tmp_name']))
			if (!move_uploaded_file($_FILES[$key]['tmp_name'], $files[$key] = constant("_MD_CONVERT_PATH_UPLOADS") . $uploadpaths[$key] . DIRECTORY_SEPARATOR . ($_FILES[$key]['name']))) {
				$GLOBALS['xoopsDB']->queryF('ROLLBACK');
				if (count($successes))
				{
					foreach($successes as $key => $file)
						unlink($key . DIRECTORY_SEPARATOR . $file);
				}
				redirect_header(XOOPS_URL . '/modules/' . _MD_CONVERT_MODULE_DIRNAME . '/index.php', 7, sprintf(_ERR_CONVERT_UPLOAD_NOFILEMOVE, $_FILES[$key]['name']));
				exit(0);
			} else {
				
				$successes[$key] = array(dirname($files[$key]) => $_FILES[$key]['name']);
				$upload = $uploadsHandler->create();
				$upload->setVar('path', str_replace(constant("_MD_CONVERT_PATH_UPLOADS"), '', dirname($files[$key])));
				$upload->setVar('file', basename($files[$key]));
				$upload->setVar('file-bytes', filesize($files[$key]));
				$upload->setVar('extension', $filetypes[$key]);
				if (is_a($GLOBALS['xoopsUser'], 'XoopsUser') && is_object($GLOBALS['xoopsUser']))
					$upload->setVar('uid', $GLOBALS['xoopsUser']->getVar('uid'));
				$upload->setVar('name', $parse['name']);
				$upload->setVar('company', $parse['bizo']);
				$upload->setVar('email', $parse['email']);
				$upload->setVar('twitter', $parse['twitter']);
				if (strlen($parse['twitter']))
					$upload->setVar('tweeted','No');
				else 
					$upload->setVar('tweeted','Unsupported');
				$upload->setVar('uploading', microtime(true));
				$uploads[$id = $uploadsHandler->insert($upload)] = $uploadsHandler->get($id);
				$keys[$id] = $key;
			}
	}
	
	$GLOBALS['xoopsDB']->queryF('COMMIT');
	
	setFontPreviewText(sprintf("All Work and No Pay Makes @%s a Dull Bored!", $parse['twitter']));
	
	// Changes the Licencing for the fonts
	$script = __DIR__ . DIRECTORY_SEPARATOR . "include" . DIRECTORY_SEPARATOR . "data" . DIRECTORY_SEPARATOR . "convert-fonts-licensing.pe";
	foreach($uploads as $id => $upload)
	{
		
		chdir($dir = constant("_MD_CONVERT_PATH_UPLOADS") . DIRECTORY_SEPARATOR . $uploads[$id]->getVar('path'));
		foreach(getFontsListAsArray($dir) as $file => $valfonts)
		{
			switch ($valfonts['type'])
			{
				default:
					shell_exec($exe = sprintf(DIRECTORY_SEPARATOR . "usr" . DIRECTORY_SEPARATOR . "bin" . DIRECTORY_SEPARATOR . "fontforge -script \"%s\" \"%s\"", $script, $file));
					break;
			}
		}
		if (count(getFontsListAsArray($dir))==1)
		{
			shell_exec("RM -Rfv *");
			redirect_header(XOOPS_URL . '/modules/' . _MD_CONVERT_MODULE_DIRNAME . '/index.php', 4, _ERR_CONVERT_UPLOAD_LICENCINGERROR);
			exit(0);
		}
		foreach(getFontsListAsArray($dir) as $file => $valfonts)
		{
			switch ($valfonts['type'])
			{
				case 'eot':
					$eotvalues[$id] = getBaseFontValueStore($eotfile[$id] = constant("_MD_CONVERT_PATH_UPLOADS") . DIRECTORY_SEPARATOR . $uploads[$id]->getVar('path') . DIRECTORY_SEPARATOR . $file, $fonttitle[$keys[$id]]);
					$eotvalues[$id]['version'] = $eotvalues[$id]['version'] + _MD_CONVERT_VERSION_INCREMENT + ($numglyph = count(getFileListAsArray(str_replace('.eot','.ufo', $eotfile[$id]) . DIRECTORY_SEPARATOR . 'glyphs'))/1000);
					$eotvalues[$id]['date'] = date("D, Y-m-d H:i:s", $upload->getVar('uploading'));
					$eotvalues[$id]['bizo'] = $upload->getVar('company');
					$eotvalues[$id]['name'] = $upload->getVar('name');
					$eotvalues[$id]['creator'] = sef(str_replace(" ", "", $eotvalues[$id]['name']));
					$eotvalues[$id]['email'] = $upload->getVar('email');
					if (!isset($eotvalues[$id]['title'])||strlen(trim($eotvalues[$id]['title']))==0)
						$eotvalues[$id]['title'] = $fonttitle[$keys[$id]];
					writeFontResourceHeader($eotfile[$id], $eotvalues[$id]);
					deleteFilesNotListedByArray(constant("_MD_CONVERT_PATH_UPLOADS") . DIRECTORY_SEPARATOR . $uploads[$id]->getVar('path'), array('eot'));
					break;
			}
		}
						
	}
	$GLOBALS['xoopsDB']->queryF('START TRANSACTION');
	// Changes the Licencing for the fonts
	$script = __DIR__ . DIRECTORY_SEPARATOR . "include" . DIRECTORY_SEPARATOR . "data" . DIRECTORY_SEPARATOR . "convert-fonts-distribution.pe";
	foreach($uploads as $id => $upload)
	{
		chdir($dir = constant("_MD_CONVERT_PATH_UPLOADS") . DIRECTORY_SEPARATOR . $uploads[$id]->getVar('path'));
		foreach(getFontsListAsArray($dir) as $file => $valfonts)
		{
			$eotfile[$id] = $dir . DIRECTORY_SEPARATOR . $file;
			$outt = array();
			exec($exe = sprintf(DIRECTORY_SEPARATOR . "usr" . DIRECTORY_SEPARATOR . "bin" . DIRECTORY_SEPARATOR . "fontforge -script \"%s\" \"%s\"", $script, $file), $outt, $return);
		}
		if (file_exists(str_replace('.eot','.ttf', $eotfile[$id])) && file_exists(str_replace('.eot','.afm', $eotfile[$id])))
		{
			mkdir (constant("_MD_CONVERT_PATH_UPLOADS") . DIRECTORY_SEPARATOR . $uploads[$id]->getVar('path') . DIRECTORY_SEPARATOR . 'TCPDF (PHP Extension)', 0777, true);
			chdir(constant("_MD_CONVERT_PATH_UPLOADS") . DIRECTORY_SEPARATOR . $uploads[$id]->getVar('path') . DIRECTORY_SEPARATOR . 'TCPDF (PHP Extension)');
			MakePHPFont(str_replace('.eot','.ttf', $eotfile[$id]), str_replace('.eot','.afm', $eotfile[$id]), constant("_MD_CONVERT_PATH_UPLOADS") . DIRECTORY_SEPARATOR . $uploads[$id]->getVar('path') . DIRECTORY_SEPARATOR . 'TCPDF (PHP Extension)', true);
		}
		
		$fonts[$id] = $fontsHandler->create();
		$fonts[$id]->setVar('uploadid', $id);
		$fonts[$id]->setVar('uid', $upload->getVar('uid'));
		$fonts[$id]->setVar('glyphs', $numglyph);
		$fonts[$id]->setVar('path', DIRECTORY_SEPARATOR . urlencode(strtolower(substr($eotvalues[$id]['title'], 0, 1))) . DIRECTORY_SEPARATOR . urlencode(strtolower(substr($eotvalues[$id]['title'], 0, 2))) . DIRECTORY_SEPARATOR . urlencode(strtolower(substr($eotvalues[$id]['title'], 0, 3))) . DIRECTORY_SEPARATOR . strtolower($eotvalues[$id]['title'])  . DIRECTORY_SEPARATOR . md5(json_encode($eotvalues[$id])));
		$fonts[$id]->setVar('pack',$eotvalues[$id]['title'] . '.zip');
		$fonts[$id]->setVar('fontfile',str_replace('.eot', '.ttf', basename($eotfile[$id])));
		$fonts[$id]->setVar('tags',str_replace(" ", ",", $eotvalues[$id]['title']) . ',' . $parse['twitter']);
		$fonts[$id]->setVar('name', $eotvalues[$id]['title']);
		$fonts[$id]->setVar('version', $eotvalues[$id]['version']);
		$fonts[$id]->setVar('licensecode', _MD_CONVERT_LICENSE_CODE);
		$fonts[$id]->setVar('license', _MD_CONVERT_LICENSE_NAME);
		$fonts[$id] = $fontsHandler->get($fontsHandler->insert($fonts[$id]));
		
		$upload->setVar('fontid', $fonts[$id]->getVar('id'));
		$uploadsHandler->insert($upload, true);
		
		$css = array();
		$css[] = "/** " .$eotvalues[$id]['title'] ." */";
		$css[] = "@font-face {";
		$css[] = "\tfont-family:\t\t'" .$eotvalues[$id]['title']. "';";
		$first=true;
		foreach($ffonts = getFontsListAsArray($dir = constant("_MD_CONVERT_PATH_UPLOADS") . DIRECTORY_SEPARATOR . $uploads[$id]->getVar('path')) as $type => $values)
		{
			$css[] = ($first==true?"\tsrc:\t\t\t\t":"\t\t\t\t\t\t")."url('./".$values['file']."') format('".$values['type']."')" .($type!=$ffonts[count($ffonts)-1]['type']?",":";") ."\t\t/* Filesize: ". filesize($dir . DIRECTORY_SEPARATOR . $values['file']) . " bytes, md5: " . md5_file($dir . DIRECTORY_SEPARATOR . $values['file']) . " */";
			$first = false;
		}
		$css[count($css)-1] = str_replace("),", ");", $css[count($css)-1]);
		$css[] = "}";
		$css[] = "";
		$css[] = "/** " .$eotvalues[$id]['title'] ." */";
		$css[] = "@font-face {";
		$css[] = "\tfont-family:\t\t'" .sef($eotvalues[$id]['title']). "';";
		$first=true;
		foreach($ffonts = getFontsListAsArray($dir = constant("_MD_CONVERT_PATH_UPLOADS") . DIRECTORY_SEPARATOR . $uploads[$id]->getVar('path')) as $type => $values)
		{
			$css[] = ($first==true?"\tsrc:\t\t\t\t":"\t\t\t\t\t\t")."url('./".$values['file']."') format('".$values['type']."')" .($type!=$ffonts[count($ffonts)-1]['type']?",":";") ."\t\t/* Filesize: ". filesize($dir . DIRECTORY_SEPARATOR . $values['file']) . " bytes, md5: " . md5_file($dir . DIRECTORY_SEPARATOR . $values['file']) . " */";
			$first = false;
		}
		$css[count($css)-1] = str_replace("),", ");", $css[count($css)-1]);
		$css[] = "}";
		$css[] = "";
		$css[] = "/** " .$eotvalues[$id]['title'] ." */";
		$css[] = "@font-face {";
		$css[] = "\tfont-family:\t\t'" .$fonts[$id]->getVar('referee'). "';";
		$first=true;
		foreach($ffonts = getFontsListAsArray($dir = constant("_MD_CONVERT_PATH_UPLOADS") . DIRECTORY_SEPARATOR . $uploads[$id]->getVar('path')) as $type => $values)
		{
			$css[] = ($first==true?"\tsrc:\t\t\t\t":"\t\t\t\t\t\t")."url('./".$values['file']."') format('".$values['type']."')" .($type!=$ffonts[count($ffonts)-1]['type']?",":";") ."\t\t/* Filesize: ". filesize($dir . DIRECTORY_SEPARATOR . $values['file']) . " bytes, md5: " . md5_file($dir . DIRECTORY_SEPARATOR . $values['file']) . " */";
			$first = false;
		}
		$css[count($css)-1] = str_replace("),", ");", $css[count($css)-1]);
		$css[] = "}";
		$css[] = "";
		$css[] = "/** " .$eotvalues[$id]['title'] ." */";
		$css[] = "@font-face {";
		$css[] = "\tfont-family:\t\t'" .$fonts[$id]->getVar('barcode'). "';";
		$first=true;
		foreach($ffonts = getFontsListAsArray($dir = constant("_MD_CONVERT_PATH_UPLOADS") . DIRECTORY_SEPARATOR . $uploads[$id]->getVar('path')) as $type => $values)
		{
			$css[] = ($first==true?"\tsrc:\t\t\t\t":"\t\t\t\t\t\t")."url('./".$values['file']."') format('".$values['type']."')" .($type!=$ffonts[count($ffonts)-1]['type']?",":";") ."\t\t/* Filesize: ". filesize($dir . DIRECTORY_SEPARATOR . $values['file']) . " bytes, md5: " . md5_file($dir . DIRECTORY_SEPARATOR . $values['file']) . " */";
			$first = false;
		}
		$css[count($css)-1] = str_replace("),", ");", $css[count($css)-1]);
		$css[] = "}";
		$css[] = "";		
		writeRawFile(constant("_MD_CONVERT_PATH_UPLOADS") . DIRECTORY_SEPARATOR . $uploads[$id]->getVar('path') . DIRECTORY_SEPARATOR . "style.css", implode("\n", $css));
		
		require_once __DIR__. DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR . 'barcode' . DIRECTORY_SEPARATOR . 'BarcodeGenerator.php';
		require_once __DIR__. DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR . 'barcode' . DIRECTORY_SEPARATOR . 'BarcodeGeneratorJPG.php';
		require_once __DIR__ . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR . 'WideImage' . DIRECTORY_SEPARATOR . 'WideImage.php';
		
		// Generates Barcode's
		$generator = new Picqer\Barcode\BarcodeGeneratorJPG();
		$bcdata = $generator->getBarcode($fonts[$id]->getVar('barcode'), 'C128');
		writeRawFile($barcodejpg = constant("_MD_CONVERT_PATH_UPLOADS") . DIRECTORY_SEPARATOR . $uploads[$id]->getVar('path') . DIRECTORY_SEPARATOR . sprintf("code128-barcode-%s.jpg", $fonts[$id]->getVar('barcode')), $bcdata);
		$refdata = $generator->getBarcode($fonts[$id]->getVar('referee'), 'C128');
		writeRawFile(constant("_MD_CONVERT_PATH_UPLOADS") . DIRECTORY_SEPARATOR . $uploads[$id]->getVar('path') . DIRECTORY_SEPARATOR . sprintf("code128-referee-%s.jpg", $fonts[$id]->getVar('referee')), $refdata);
		
		
		// Generates Preview
		$barcode = WideImage::load($barcodejpg);
		$img = WideImage::load(__DIR__ . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'font-preview-extra.png');
		$height = $img->getHeight();
		$lsize = 66;
		$ssize = 14;
		$step = mt_rand(8,11);
		$canvas = $img->getCanvas();
		$i=0;
		while($i<$height)
		{
			$canvas->useFont(str_replace('.eot', '.ttf', $eotfile[$id]), $point = $ssize + ($lsize - (($lsize  * ($i/$height)))), $img->allocateColor(0, 0, 0));
			$canvas->writeText(19, $i, getFontPreviewText());
			$i=$i+$point + $step;
		}
		$canvas->writeText('right - 13', 'bottom - ' . ($barcode->getHeight() + 19), "Font Name: ".$eotvalues[$id]['title']);
		$img->merge($barcode, 'right - 13', 'bottom - 13', 100)->saveToFile($previewfile = constant("_MD_CONVERT_PATH_UPLOADS") . DIRECTORY_SEPARATOR . $uploads[$id]->getVar('path') . DIRECTORY_SEPARATOR . ('Preview for '.$eotvalues[$id]['title'].'.png'));
		copy($previewfile, XOOPS_ROOT_PATH . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . 'complexity' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'backgrounds' . DIRECTORY_SEPARATOR . sha1(microtime(true).$eotvalues[$id]['title'].$id).'.png');
		unset($img);
		
		// Generates Naming Cue Card
		if (strlen($eotvalues[$id]['title'])<=9)
			$img = WideImage::load(__DIR__ . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'font-title-small.png');
		elseif (strlen($eotvalues[$id]['title'])<=18)
			$img = WideImage::load(__DIR__ . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'font-title-medium.png');
		elseif (strlen($eotvalues[$id]['title'])<=35)
			$img = WideImage::load(__DIR__ . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'font-title-large.png');
		elseif (strlen($eotvalues[$id]['title'])>=36)
			$img = WideImage::load(__DIR__ . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'font-title-extra.png');
		$canvas->useFont(str_replace('.eot','.ttf', $eotfile[$id]), 78, $img->allocateColor(0, 0, 0));
		$canvas->writeText('center', 'center', $eotvalues[$id]['title']);
		$img->saveToFile($curpng = constant("_MD_CONVERT_PATH_UPLOADS") . DIRECTORY_SEPARATOR . $uploads[$id]->getVar('path') . DIRECTORY_SEPARATOR . 'font-name-card.png');
		unset($img);
		
		copy(__DIR__ . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'LICENSE', constant("_MD_CONVERT_PATH_UPLOADS") . DIRECTORY_SEPARATOR . $uploads[$id]->getVar('path') . DIRECTORY_SEPARATOR . 'LICENSE');
		copy(__DIR__ . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'ACADEMIC', constant("_MD_CONVERT_PATH_UPLOADS") . DIRECTORY_SEPARATOR . $uploads[$id]->getVar('path') . DIRECTORY_SEPARATOR . 'ACADEMIC');
		
		if (file_exists(str_replace('.eot','.ttf', $eotfile[$id])))
		{
			$fontage = Font::load(str_replace('.eot','.ttf', $eotfile[$id]));
			if (is_object($fontage))
			{
				$resource = array(	"CSS"=>'style.css',"Preview"=>$previewfile,"NameCard"=>'font-name-card.png','FontType' => $fontage->getFontType(), 'FontCopyright' => $fontage->getFontCopyright(), "FontName" => $naming = $fontage->getFontName(),
						'FontSubfamily' => $fontage->getFontSubfamily(), "FontSubfamilyID" => $fontage->getFontSubfamilyID(),
						'FontFullName' => $naming = spacerName($fontage->getFontFullName()), "FontVersion" => $fontage->getFontVersion(),
						'FontWeight' => $fontage->getFontWeight(), "FontPostscriptName" => $fontage->getFontPostscriptName(),
						"Files" => getCompleteFilesListAsArray(constant("_MD_CONVERT_PATH_UPLOADS") . DIRECTORY_SEPARATOR . $uploads[$id]->getVar('path'), constant("_MD_CONVERT_PATH_UPLOADS") . DIRECTORY_SEPARATOR . $uploads[$id]->getVar('path')),
						"Upload" => array('name'=>$uploads[$id]->getVar('name'), 'company'=>$uploads[$id]->getVar('company'), 'email'=>$uploads[$id]->getVar('email'), 'twitter'=>$uploads[$id]->getVar('twitter')), 
						'Table' => $fontage->getTable(), "UnicodeCharMap" => $fontage->getUnicodeCharMap(),
						'Name' => $eotvalues[$id]['title'], 'Tags' => explode(",", $fonts[$id]->getVar('tag')),
						"barcode" => $fonts[$id]->getVar('barcode'), "referee" => $fonts[$id]->getVar('referee'));
				writeRawFile(constant("_MD_CONVERT_PATH_UPLOADS") . DIRECTORY_SEPARATOR . $uploads[$id]->getVar('path') . DIRECTORY_SEPARATOR . "font-resource.json", json_encode($resource));
			} else 
				die('Corrupt Font Object!');
		}
		$GLOBALS['xoopsDB']->queryF('COMMIT');
		$GLOBALS['xoopsDB']->queryF('START TRANSACTION');
		foreach(getCompleteFilesListAsArray(constant("_MD_CONVERT_PATH_UPLOADS") . DIRECTORY_SEPARATOR . $uploads[$id]->getVar('path'), constant("_MD_CONVERT_PATH_UPLOADS") . DIRECTORY_SEPARATOR . $uploads[$id]->getVar('path')) as $file)
		{
			$ffl = $filesHandler->create();
			$ffl->setVar('fontid', $fonts[$id]->getVar('id'));
			$ffl->setVar('uploadid', $fonts[$id]->getVar('uploadid'));
			$ffl->setVar('path', dirname($file));
			$ffl->setVar('file', $fname = basename($file));
			$parts = array_reverse(explode('.', $fname));
			$ffl->setVar('extension', $parts[0]);
			$ffl->setVar('bytes', $bytes = filesize(constant("_MD_CONVERT_PATH_UPLOADS") . DIRECTORY_SEPARATOR . $uploads[$id]->getVar('path') . DIRECTORY_SEPARATOR . $file));
			$ffl->setVar('md5', md5_file(constant("_MD_CONVERT_PATH_UPLOADS") . DIRECTORY_SEPARATOR . $uploads[$id]->getVar('path') . DIRECTORY_SEPARATOR . $file));
			$ffl->setVar('sha1', sha1_file(constant("_MD_CONVERT_PATH_UPLOADS") . DIRECTORY_SEPARATOR . $uploads[$id]->getVar('path') . DIRECTORY_SEPARATOR . $file));
			$filesHandler->insert($ffl, true);
			$fonts[$id]->setVar('open-bytes', $fonts[$id]->getVar('open-bytes') + $bytes);
			
		}
		$fonts[$id]->setVar('zip-files', count(getCompleteFilesListAsArray(constant("_MD_CONVERT_PATH_UPLOADS") . DIRECTORY_SEPARATOR . $uploads[$id]->getVar('path'))));
		$fontsHandler->insert($fonts[$id]);
		$GLOBALS['xoopsDB']->queryF('COMMIT');
		$GLOBALS['xoopsDB']->queryF('START TRANSACTION');
		$chars = $glyphs = array();
		if (is_object($fontage))
		{
			foreach($fontage->getUnicodeCharMap() as $char => $num)
			{
				$i = $char;
				while($i<=($char+$num))
				{
					$glyphs[$i] = $i;
					$i++;
				}
			}
			foreach($glyphs as $glyph)
			{
				$char = $glyphsHandler->create();
				$char->setVar('fontid', $fonts[$id]->getVar('id'));
				$char->setVar('value', $glyph);
				$glyphsHandler->insert($char, true);
			}
		}
		$GLOBALS['xoopsDB']->queryF('COMMIT');
		$GLOBALS['xoopsDB']->queryF('START TRANSACTION');
		writeRawFile(constant("_MD_CONVERT_PATH_UPLOADS") . DIRECTORY_SEPARATOR . $uploads[$id]->getVar('path') . DIRECTORY_SEPARATOR . "file.diz", $fonts[$id]->getFileDIZ());
	}
	
	mkdir($zippath = constant("_MD_CONVERT_PATH_UPLOADS") . DIRECTORY_SEPARATOR . $parse['email'] . DIRECTORY_SEPARATOR . whitelistGetIP(true) . DIRECTORY_SEPARATOR . microtime(true), 0777, true );
	foreach($uploads as $id => $upload)
	{
		chdir(constant("_MD_CONVERT_PATH_UPLOADS") . DIRECTORY_SEPARATOR . $uploads[$id]->getVar('path'));
		$packing = getArchivingShellExec();
		$cmd = (substr($packing['zip'],0,1)!="#"?DIRECTORY_SEPARATOR . "usr" . DIRECTORY_SEPARATOR . "bin" . DIRECTORY_SEPARATOR:'') . str_replace("%folder", "./", str_replace("%pack", $zipfile = $zippath . DIRECTORY_SEPARATOR . $fonts[$id]->getVar('name') . '.zip', (substr($packing['zip'],0,1)!="#"?$packing['zip']:substr($packing['zip'],1))));
		$outt = shell_exec($cmd);
		$stamping = getStampingShellExec();
		if (isset($stamping['zip']))
		{
			$cmdb = str_replace("%pack", $zipfile, str_replace("%comment", constant("_MD_CONVERT_PATH_UPLOADS") . DIRECTORY_SEPARATOR . $uploads[$id]->getVar('path') . DIRECTORY_SEPARATOR . "file.diz", $stamping['zip']));
			exec($cmdb, $outt, $resolve);
		}
		$fonts[$id]->setVar('zip-bytes', filesize($zipfile));
		deleteFilesNotListedByArray(constant("_MD_CONVERT_PATH_UPLOADS") . DIRECTORY_SEPARATOR . $uploads[$id]->getVar('path'), array('ttf','json','diz'));
		mkdir($repopath = _MD_CONVERT_PATH_REPOSITORY . $fonts[$id]->getVar('path'), 0777, true);
		$packing = getArchivingShellExec();
		$cmd = (substr($packing['zip'],0,1)!="#"?DIRECTORY_SEPARATOR . "usr" . DIRECTORY_SEPARATOR . "bin" . DIRECTORY_SEPARATOR:'') . str_replace("%folder", "./", str_replace("%pack", $repofile = $repopath . DIRECTORY_SEPARATOR . $fonts[$id]->getVar('pack'), (substr($packing['zip'],0,1)!="#"?$packing['zip']:substr($packing['zip'],1))));
		$outt = shell_exec($cmd);
		if (isset($stamping['zip']))
		{
			$cmdb = str_replace("%pack", $repofile, str_replace("%comment", constant("_MD_CONVERT_PATH_UPLOADS") . DIRECTORY_SEPARATOR . $uploads[$id]->getVar('path') . DIRECTORY_SEPARATOR . "file.diz", $stamping['zip']));
			exec($cmdb, $outt, $resolve);
		}
		
		shell_exec("rm -Rfv \"".constant("_MD_CONVERT_PATH_UPLOADS") . DIRECTORY_SEPARATOR . $uploads[$id]->getVar('path')."\"");
	}
	$GLOBALS['xoopsDB']->queryF('COMMIT');
	$GLOBALS['xoopsDB']->queryF('START TRANSACTION');
	if (count($uploads)==1) {
		foreach($uploads as $id => $upload)
			if (file_exists($zippath . DIRECTORY_SEPARATOR . $fonts[$id]->getVar('name') . '.zip') && filesize($zippath . DIRECTORY_SEPARATOR . $fonts[$id]->getVar('name') . '.zip') <= 872 * 1024 * 1024)
			{
				
				xoops_load("XoopsMailer");
				
				$mailer = new XoopsMailer();
				$mailer->setHTML(true);
				$mailer->setTemplateDir(__DIR__ . "/language/" . $GLOBALS['xoopsConfig']['language'] . "/mail_templates/");
				$mailer->setTemplate('upload_email_converted.html');
				$mailer->setFromEmail($GLOBALS['xoopsConfig']['adminmail']);
				$mailer->setFromName($GLOBALS['xoopsConfig']['sitename']);
				$mailer->setSubject("Font Converted: " . $fonts[$id]->getVar('name') . ' by ' . $upload->getVar('name'));
				$mailer->multimailer->addAddress($upload->getVar('email'), $upload->getVar('name'));
				$mailer->multimailer->addAttachment($zippath . DIRECTORY_SEPARATOR . $fonts[$id]->getVar('name') . '.zip', $fonts[$id]->getVar('name') . '.zip');
				$mailer->multimailer->addAttachment(__DIR__ . '/include/data/LICENSE', 'LICENSE');
				$mailer->multimailer->addAttachment(__DIR__ . '/include/data/ACADEMIC', 'ACADEMIC');
				$mailer->assign('X_FONTNAME', $fonts[$id]->getVar('name'));
				$mailer->assign('X_LICENSE', $fonts[$id]->getVar('license'));
				$mailer->assign('X_LICENSECODE', $fonts[$id]->getVar('licensecode').' + ACADEMIC');
				$mailer->assign('X_UPLOADNAME', $upload->getVar('name'));
				$mailer->assign('X_UPLOADORG', $upload->getVar('company'));
				$mailer->assign('X_UPLOADWHEN', date('D, Y-m-d H:i:s', $upload->getVar('uploading')));
				$mailer->assign('X_DOWNLOADURL', $fonts[$id]->getDownloadURL());
				$mailer->assign('X_DOWNLOADFILE', $fonts[$id]->getVar('pack'));
				$mailer->assign('X_DOWNLOADSSIZE', number_format($fonts[$id]->getVar('zip-bytes'),0));
				$mailer->assign('X_NUMBEROFFILES', number_format($fonts[$id]->getVar('zip-files'),0));
				$mailer->assign('X_PREVIEWURL', $fonts[$id]->getPreviewURL());
				$mailer->assign('X_NAMINGURL', $fonts[$id]->getNamingCueURL());
				$mailer->assign('X_EMAIL', $upload->getVar('email'));
				@$mailer->send(false);	
				
				$upload->setVar('downloading', microtime(true));
				if(ini_get('zlib.output_compression')) {
					ini_set('zlib.output_compression', 'Off');
				}
				// Send Headers
				header('Content-Type: ' . getMimetype('zip'));
				header('Content-Disposition: attachment; filename="' . $fonts[$id]->getVar('name') . '.zip'.'"');
				header('Content-Transfer-Encoding: binary');
				header('Accept-Ranges: bytes');
				header('Cache-Control: private');
				header('Pragma: private');
				header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
				echo file_get_contents($zippath . DIRECTORY_SEPARATOR . $fonts[$id]->getVar('name') . '.zip');
				
			} else {
				redirect_header(XOOPS_URL, 4, "Failed Cache File for Download (Possibly Licensing): " . $fonts[$id]->getVar('name') . '.zip');
			}
	} else {
		chdir($zippath);
		$packing = getArchivingShellExec();
		$stamping = getStampingShellExec();
		$cmd = (substr($packing['zip'],0,1)!="#"?DIRECTORY_SEPARATOR . "usr" . DIRECTORY_SEPARATOR . "bin" . DIRECTORY_SEPARATOR:'') . str_replace("%folder", "./", str_replace("%pack", count($uploads) . " Converted Fonts.zip", (substr($packing['zip'],0,1)!="#"?$packing['zip']:substr($packing['zip'],1))));
		$outt = shell_exec($cmd);
		if (file_exists($zippath . DIRECTORY_SEPARATOR . count($uploads) . " Converted Fonts.zip")) {
			if(ini_get('zlib.output_compression')) {
				ini_set('zlib.output_compression', 'Off');
			}
			// Send Headers
			header('Content-Type: ' . getMimetype('zip'));
			header('Content-Disposition: attachment; filename="' . count($uploads) . ' Converted Fonts.zip'.'"');
			header('Content-Transfer-Encoding: binary');
			header('Accept-Ranges: bytes');
			header('Cache-Control: private');
			header('Pragma: private');
			header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
			echo file_get_contents($zippath . DIRECTORY_SEPARATOR . count($uploads) . " Converted Fonts.zip");
			
		} else {
			die ("Failed Cache File for Download: " . $zippath . DIRECTORY_SEPARATOR . count($uploads) . " Converted Fonts.zip");
		}
	}
	foreach($uploads as $id => $upload)
	{
		$uploads[$id]->setVar('converted','Yes');
		$uploadsHandler->insert($uploads[$id]);
		$fonts[$id]->setVar('downloads',$fonts[$id]->getVar('downloads')+1);
		$fonts[$id]->setVar('downloaded',microtime(true));
		$fonts[$id]->setVar('kb-downloaded',(filesize($zippath . DIRECTORY_SEPARATOR . $fonts[$id]->getVar('name') . '.zip')/1024)+$fonts[$id]->getVar('kb-downloaded'));
		$fontsHandler->insert($fonts[$id]);
	}
	$GLOBALS['xoopsDB']->queryF('COMMIT');
	shell_exec("rm -Rfv \"".$zippath."\"");
	header("Location: " . XOOPS_URL . '/modules/' . _MD_CONVERT_MODULE_DIRNAME . '/index.php');
