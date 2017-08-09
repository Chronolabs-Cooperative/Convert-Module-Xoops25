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
		redirect_header(XOOPS_URL . '/modules/convert/index.php', 4, _ERR_CONVERT_NAMING_NOIDSPECIFIED);
		exit(0);
	}
	
	$fontHandler = xoops_getModuleHandler('fonts',_MD_CONVERT_MODULE_DIRNAME);
	
	if (!$font = $fontHandler->getByHash($_GET['id']))
	{
		redirect_header(XOOPS_URL . '/modules/convert/index.php', 4, _ERR_CONVERT_NAMING_IDNOTFOUND);
		exit(0);
	}
	
	$uploadHandler = xoops_getModuleHandler('uploads',_MD_CONVERT_MODULE_DIRNAME);
	$upload = $uploadHandler->get($font->getVar('uploadid'));
	
	if ($GLOBALS['convertConfigsList']['htaccess']) {
		if (!strpos($font->getDownloadURL(), $_SERVER['REQUEST_URI'])) {
			header('Location: ' . $font->getDownloadURL());
			exit(0);
		}
	}
	
	
	use FontLib\Font;
	require_once __DIR__ . '/class/FontLib/Autoloader.php';
	
	xoops_loadLanguage('errors', _MD_CONVERT_MODULE_DIRNAME);
	xoops_loadLanguage('modinfo', _MD_CONVERT_MODULE_DIRNAME);
	
	$uploadpaths = DIRECTORY_SEPARATOR . $upload->getVar('email') . DIRECTORY_SEPARATOR . microtime(true) . DIRECTORY_SEPARATOR . md5($key.json_encode($upload->getValues(array_keys($upload->vars))).microtime(true));
	if (!is_dir(constant("_MD_CONVERT_PATH_UPLOADS") . $uploadpaths)) {
		if (!mkdir(constant("_MD_CONVERT_PATH_UPLOADS") . $uploadpaths, 0777, true)) {
			$error[] = sprintf(_ERR_CONVERT_UPLOAD_NOMAKEFOLDER, constant("_MD_CONVERT_PATH_UPLOADS") . $uploadpaths);
		}
	}
								
	//echo basename(__FILE__). "::" . __LINE__ ."<br/>";
	if (count($error)>0)
	{
		redirect_header(XOOPS_URL . '/modules/' . _MD_CONVERT_MODULE_DIRNAME . '/index.php', 7, implode("<br />", $error));
		exit(0);
	}
						
	//echo basename(__FILE__). "::" . __LINE__ ."<br/>";
	chdir($dir = constant("_MD_CONVERT_PATH_UPLOADS") . DIRECTORY_SEPARATOR . $uploadpaths);
	copy($font->getCachedFile(), $dir . DIRECTORY_SEPARATOR . $font->getVar('fontfile'));
	$script = __DIR__ . DIRECTORY_SEPARATOR . "include" . DIRECTORY_SEPARATOR . "data" . DIRECTORY_SEPARATOR . "convert-fonts-distribution.pe";
	foreach(getFontsListAsArray($dir) as $file => $valfonts)
	{
		$outt = array();
		exec($exe = sprintf(DIRECTORY_SEPARATOR . "usr" . DIRECTORY_SEPARATOR . "bin" . DIRECTORY_SEPARATOR . "fontforge -script \"%s\" \"%s\"", $script, $file), $outt, $return);
	}
	foreach(getFontsListAsArray($dir) as $file => $valfonts)
	{
		switch ($valfonts['type'])
		{
			case "eot":
				$eotfile = $dir. DIRECTORY_SEPARATOR .$file;
				break;	
		}
	}
	if (file_exists(str_replace('.eot','.ttf', $eotfile)) && file_exists(str_replace('.eot','.afm', $eotfile)))
	{
		mkdir (constant("_MD_CONVERT_PATH_UPLOADS") . DIRECTORY_SEPARATOR . $uploadpaths . DIRECTORY_SEPARATOR . 'TCPDF (PHP Extension)', 0777, true);
		chdir(constant("_MD_CONVERT_PATH_UPLOADS") . DIRECTORY_SEPARATOR . $uploadpaths . DIRECTORY_SEPARATOR . 'TCPDF (PHP Extension)');
		MakePHPFont(str_replace('.eot','.ttf', $eotfile), str_replace('.eot','.afm', $eotfile), constant("_MD_CONVERT_PATH_UPLOADS") . DIRECTORY_SEPARATOR . $uploadpaths . DIRECTORY_SEPARATOR . 'TCPDF (PHP Extension)', true);
	}
	//echo basename(__FILE__). "::" . __LINE__ ."<br/>";
	$css = array();
	$css[] = "/** " .$font->getVar('name') ." */";
	$css[] = "@font-face {";
	$css[] = "\tfont-family:\t\t'" .$font->getVar('name'). "';";
	$first=true;
	foreach($ffonts = getFontsListAsArray($dir = constant("_MD_CONVERT_PATH_UPLOADS") . DIRECTORY_SEPARATOR . $uploadpaths) as $type => $values)
	{
		$css[] = ($first==true?"\tsrc:\t\t\t\t":"\t\t\t\t\t\t")."url('./".$values['file']."') format('".$values['type']."')" .($type!=$ffonts[count($ffonts)-1]['type']?",":";") ."\t\t/* Filesize: ". filesize($dir . DIRECTORY_SEPARATOR . $values['file']) . " bytes, md5: " . md5_file($dir . DIRECTORY_SEPARATOR . $values['file']) . " */";
		$first = false;
	}
	$css[count($css)-1] = str_replace("),", ");", $css[count($css)-1]);
	$css[] = "}";
	$css[] = "";
	$css[] = "/** " .$font->getVar('name') ." */";
	$css[] = "@font-face {";
	$css[] = "\tfont-family:\t\t'" .sef($font->getVar('name')). "';";
	$first=true;
	foreach($ffonts = getFontsListAsArray($dir = constant("_MD_CONVERT_PATH_UPLOADS") . DIRECTORY_SEPARATOR . $uploadpaths) as $type => $values)
	{
		$css[] = ($first==true?"\tsrc:\t\t\t\t":"\t\t\t\t\t\t")."url('./".$values['file']."') format('".$values['type']."')" .($type!=$ffonts[count($ffonts)-1]['type']?",":";") ."\t\t/* Filesize: ". filesize($dir . DIRECTORY_SEPARATOR . $values['file']) . " bytes, md5: " . md5_file($dir . DIRECTORY_SEPARATOR . $values['file']) . " */";
		$first = false;
	}
	$css[count($css)-1] = str_replace("),", ");", $css[count($css)-1]);
	$css[] = "}";
	$css[] = "";
	$css[] = "/** " .$font->getVar('name') ." */";
	$css[] = "@font-face {";
	$css[] = "\tfont-family:\t\t'" .$font->getVar('referee'). "';";
	$first=true;
	foreach($ffonts = getFontsListAsArray($dir = constant("_MD_CONVERT_PATH_UPLOADS") . DIRECTORY_SEPARATOR . $uploadpaths) as $type => $values)
	{
		$css[] = ($first==true?"\tsrc:\t\t\t\t":"\t\t\t\t\t\t")."url('./".$values['file']."') format('".$values['type']."')" .($type!=$ffonts[count($ffonts)-1]['type']?",":";") ."\t\t/* Filesize: ". filesize($dir . DIRECTORY_SEPARATOR . $values['file']) . " bytes, md5: " . md5_file($dir . DIRECTORY_SEPARATOR . $values['file']) . " */";
		$first = false;
	}
	$css[count($css)-1] = str_replace("),", ");", $css[count($css)-1]);
	$css[] = "}";
	$css[] = "";
	$css[] = "/** " .$font->getVar('name') ." */";
	$css[] = "@font-face {";
	$css[] = "\tfont-family:\t\t'" .$font->getVar('barcode'). "';";
	$first=true;
	foreach($ffonts = getFontsListAsArray($dir = constant("_MD_CONVERT_PATH_UPLOADS") . DIRECTORY_SEPARATOR . $uploadpaths) as $type => $values)
	{
		$css[] = ($first==true?"\tsrc:\t\t\t\t":"\t\t\t\t\t\t")."url('./".$values['file']."') format('".$values['type']."')" .($type!=$ffonts[count($ffonts)-1]['type']?",":";") ."\t\t/* Filesize: ". filesize($dir . DIRECTORY_SEPARATOR . $values['file']) . " bytes, md5: " . md5_file($dir . DIRECTORY_SEPARATOR . $values['file']) . " */";
		$first = false;
	}
	$css[count($css)-1] = str_replace("),", ");", $css[count($css)-1]);
	$css[] = "}";
	$css[] = "";
	writeRawFile(constant("_MD_CONVERT_PATH_UPLOADS") . DIRECTORY_SEPARATOR . $uploadpaths . DIRECTORY_SEPARATOR . "style.css", implode("\n", $css));
	//echo basename(__FILE__). "::" . __LINE__ ."<br/>";
	require_once __DIR__. DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR . 'barcode' . DIRECTORY_SEPARATOR . 'BarcodeGenerator.php';
	require_once __DIR__. DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR . 'barcode' . DIRECTORY_SEPARATOR . 'BarcodeGeneratorJPG.php';
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR . 'WideImage' . DIRECTORY_SEPARATOR . 'WideImage.php';

	// Generates Barcode's
	$generator = new Picqer\Barcode\BarcodeGeneratorJPG();
	$bcdata = $generator->getBarcode($font->getVar('barcode'), 'C128');
	writeRawFile($barcodejpg = constant("_MD_CONVERT_PATH_UPLOADS") . DIRECTORY_SEPARATOR . $uploadpaths . DIRECTORY_SEPARATOR . sprintf("code128-barcode-%s.jpg", $font->getVar('barcode')), $bcdata);
	$refdata = $generator->getBarcode($font->getVar('referee'), 'C128');
	writeRawFile(constant("_MD_CONVERT_PATH_UPLOADS") . DIRECTORY_SEPARATOR . $uploadpaths . DIRECTORY_SEPARATOR . sprintf("code128-referee-%s.jpg", $font->getVar('referee')), $refdata);
	//echo basename(__FILE__). "::" . __LINE__ ."<br/>";

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
		$canvas->useFont(str_replace('.eot', '.ttf', $eotfile), $point = $ssize + ($lsize - (($lsize  * ($i/$height)))), $img->allocateColor(0, 0, 0));
		$canvas->writeText(19, $i, getFontPreviewText());
		$i=$i+$point + $step;
	}
	$canvas->writeText('right - 13', 'bottom - ' . ($barcode->getHeight() + 19), "Font Name: ".$font->getVar('name'));
	$img->merge($barcode, 'right - 13', 'bottom - 13', 100)->saveToFile($previewfile = constant("_MD_CONVERT_PATH_UPLOADS") . DIRECTORY_SEPARATOR . $uploadpaths . DIRECTORY_SEPARATOR . ('Preview for '.$font->getVar('name').'.png'));
	copy($previewfile, XOOPS_ROOT_PATH . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . 'complexity' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'backgrounds' . DIRECTORY_SEPARATOR . sha1(microtime(true).$font->getVar('name').$id).'.png');
	unset($img);
	//echo basename(__FILE__). "::" . __LINE__ ."<br/>";
	// Generates Naming Cue Card
	if (strlen($font->getVar('name'))<=9)
		$img = WideImage::load(__DIR__ . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'font-title-small.png');
	elseif (strlen($font->getVar('name'))<=18)
		$img = WideImage::load(__DIR__ . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'font-title-medium.png');
	elseif (strlen($font->getVar('name'))<=35)
		$img = WideImage::load(__DIR__ . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'font-title-large.png');
	elseif (strlen($font->getVar('name'))>=36)
	$img = WideImage::load(__DIR__ . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'font-title-extra.png');
	$canvas->useFont(str_replace('.eot','.ttf', $eotfile), 78, $img->allocateColor(0, 0, 0));
	$canvas->writeText('center', 'center', $font->getVar('name'));
	$img->saveToFile($curpng = constant("_MD_CONVERT_PATH_UPLOADS") . DIRECTORY_SEPARATOR . $uploadpaths . DIRECTORY_SEPARATOR . 'font-name-card.png');
	unset($img);
	//echo basename(__FILE__). "::" . __LINE__ ."<br/>";
	copy(__DIR__ . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'LICENSE', constant("_MD_CONVERT_PATH_UPLOADS") . DIRECTORY_SEPARATOR . $uploadpaths . DIRECTORY_SEPARATOR . 'LICENSE');
	copy(__DIR__ . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'ACADEMIC', constant("_MD_CONVERT_PATH_UPLOADS") . DIRECTORY_SEPARATOR . $uploadpaths . DIRECTORY_SEPARATOR . 'ACADEMIC');
	//echo basename(__FILE__). "::" . __LINE__ ."<br/>";
	if (file_exists(str_replace('.eot','.ttf', $eotfile)))
	{
		$fontage = Font::load(str_replace('.eot','.ttf', $eotfile));
		if (is_object($fontage))
		{
			$resource = array(	"CSS"=>'style.css',"Preview"=>$previewfile,"NameCard"=>'font-name-card.png','FontType' => $fontage->getFontType(), 'FontCopyright' => $fontage->getFontCopyright(), "FontName" => $naming = $fontage->getFontName(),
					'FontSubfamily' => $fontage->getFontSubfamily(), "FontSubfamilyID" => $fontage->getFontSubfamilyID(),
					'FontFullName' => $naming = spacerName($fontage->getFontFullName()), "FontVersion" => $fontage->getFontVersion(),
					'FontWeight' => $fontage->getFontWeight(), "FontPostscriptName" => $fontage->getFontPostscriptName(),
					"Files" => getCompleteFilesListAsArray(constant("_MD_CONVERT_PATH_UPLOADS") . DIRECTORY_SEPARATOR . $uploadpaths, constant("_MD_CONVERT_PATH_UPLOADS") . DIRECTORY_SEPARATOR . $uploadpaths),
					"Upload" => array('name'=>$upload->getVar('name'), 'company'=>$upload->getVar('company'), 'email'=>$upload->getVar('email'), 'twitter'=>$upload->getVar('twitter')),
					'Table' => $fontage->getTable(), "UnicodeCharMap" => $fontage->getUnicodeCharMap(),
					'Name' => $font->getVar('name'), 'Tags' => explode(",", $font->getVar('tag')),
					"barcode" => $font->getVar('barcode'), "referee" => $font->getVar('referee'));
			writeRawFile(constant("_MD_CONVERT_PATH_UPLOADS") . DIRECTORY_SEPARATOR . $uploadpaths . DIRECTORY_SEPARATOR . "font-resource.json", json_encode($resource));
		} else
			die('Corrupt Font Object!');
	}
	
	mkdir($zippath = constant("_MD_CONVERT_PATH_UPLOADS") . DIRECTORY_SEPARATOR . $upload->getVar('email') . DIRECTORY_SEPARATOR . whitelistGetIP(true) . DIRECTORY_SEPARATOR . microtime(true), 0777, true );
	chdir(constant("_MD_CONVERT_PATH_UPLOADS") . DIRECTORY_SEPARATOR . $uploadpaths);
	$packing = getArchivingShellExec();
	$cmd = (substr($packing['zip'],0,1)!="#"?DIRECTORY_SEPARATOR . "usr" . DIRECTORY_SEPARATOR . "bin" . DIRECTORY_SEPARATOR:'') . str_replace("%folder", "./", str_replace("%pack", $zipfile = $zippath . DIRECTORY_SEPARATOR . $font->getVar('pack'), (substr($packing['zip'],0,1)!="#"?$packing['zip']:substr($packing['zip'],1))));
	$outt = shell_exec($cmd);
	$stamping = getStampingShellExec();
	if (isset($stamping['zip']))
	{
		$cmdb = str_replace("%pack", $zipfile, str_replace("%comment", constant("_MD_CONVERT_PATH_UPLOADS") . DIRECTORY_SEPARATOR . $uploadpaths . DIRECTORY_SEPARATOR . "file.diz", $stamping['zip']));
		exec($cmdb, $outt, $resolve);
	}
							
	shell_exec("rm -Rfv \"".constant("_MD_CONVERT_PATH_UPLOADS") . DIRECTORY_SEPARATOR . $uploadpaths."\"");
						
	if (file_exists($zipfile)) {
		$upload->setVar('downloading', microtime(true));
		if(ini_get('zlib.output_compression')) {
			ini_set('zlib.output_compression', 'Off');
		}
		// Send Headers
		header('Content-Type: ' . getMimetype('zip'));
		header('Content-Disposition: attachment; filename="' . basename($zipfile) . '"');
		header('Content-Transfer-Encoding: binary');
		header('Accept-Ranges: bytes');
		header('Cache-Control: private');
		header('Pragma: private');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		echo file_get_contents($zipfile);

	} else {
		die ("Failed Cache File for Download: " . $zippath . DIRECTORY_SEPARATOR . $font->getVar('name') . '.zip');
	}


	$font->setVar('downloads',$font->getVar('downloads')+1);
	$font->setVar('downloaded',microtime(true));
	$font->setVar('kb-downloaded',(filesize($zipfile)/1024)+$font->getVar('kb-downloaded'));
	$fontHandler->insert($font);
	$uploadHandler->insert($upload);
	shell_exec("rm -Rfv \"".$zippath."\"");
	header("Location: " . XOOPS_URL . '/modules/' . _MD_CONVERT_MODULE_DIRNAME . '/index.php');
	
		
?>