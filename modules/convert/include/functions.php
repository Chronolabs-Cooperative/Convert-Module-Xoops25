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

if (!defined(_MD_CONVERT_MODULE_DIRNAME))
	define('_MD_CONVERT_MODULE_DIRNAME', _MD_CONVERT_MODULE_DIRNAME);


if (!function_exists("fontsUseragentSupportedArray")) {
	/**
	 * Returns supported fonting formats with HTTP User-Agent
	 *
	 * @return array;
	 */
	function fontsUseragentSupportedArray()
	{
		$return = array();
		if (isset($_GET['version']) && !empty($_GET['version']))
			$version = (string)$_GET['version'];
		else
			$version = (string)"v2";
		$ua = explode( " " , str_replace(array("\"","'",";",":","(",")","\\","/"), " ", $_SERVER['HTTP_USER_AGENT']) );
		$fontlist = __DIR__ . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'default-useragent-'.$version.'.diz';
		if (!isset($ua[0]) && empty($ua[0]) && !isset($ua[1]) && empty($ua[1]) && !file_exists($fontlist = __DIR__ . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . strtolower($ua[0]).'-'.strtolower($ua[1]).'-useragent-'.$version.'.diz'))
		{
			foreach(cleanWhitespaces(file($fontlist)) as $out)
			{
				$puts = explode("||", $out);
				$return[$puts[0]]=$puts[1];
			}
		}
		if (empty($return))
			foreach(cleanWhitespaces(file($fontlist)) as $out)
			{
				$puts = explode("||", $out);
				$return[$puts[0]]=$puts[1];
			}
		return $return;
	}
}

	
if (!function_exists("getEnumeratorValues")) {
	/**
	 * Loads a field enumerator values
	 *
	 * @param string $filename
	 * @param string $variable
	 * @return array():
	 */
	function getEnumeratorValues($filename = '', $variable = '')
	{
		$variable = str_replace(array('-', ' '), "_", $variable);
		static $ret = array();
		if (!isset($ret[basename($file)]))
			if (file_exists($file = __DIR__ . DIRECTORY_SEPARATOR . 'enumerators' . DIRECTORY_SEPARATOR . "$variable__" . str_replace("php", "diz", basename($filename))))
				foreach( file($file) as $id => $value )
					if (!empty($value))
						$ret[basename($file)][$value] = $value;
						return $ret[basename($file)];
	}
}

if (!function_exists("pleaseDecryptPassword")) {
	/**
	 * Decrypts a password
	 *
	 * @param string $password
	 * @param string $cryptiopass
	 * @return string:
	 */
	function pleaseDecryptPassword($password = '', $cryptiopass = '')
	{
		$sql = "SELECT AES_DECRYPT(%s, %s) as `crypteec`";
		list($result) = $GLOBALS["xoopsDB"]->fetchRow($GLOBALS["xoopsDB"]->queryF(sprintf($sql, $GLOBALS["xoopsDB"]->quote($password), $GLOBALS["xoopsDB"]->quote($cryptiopass))));
		return $result;
	}
}


if (!function_exists("pleaseEncryptPassword")) {
	/**
	 * Encrypts a password
	 *
	 * @param string $password
	 * @param string $cryptiopass
	 * @return string:
	 */
	function pleaseEncryptPassword($password = '', $cryptiopass = '')
	{
		$sql = "SELECT AES_ENCRYPT(%s, %s) as `encrypic`";
		list($result) = $GLOBALS["xoopsDB"]->fetchRow($GLOBALS["xoopsDB"]->queryF(sprintf($sql, $GLOBALS["xoopsDB"]->quote($password), $GLOBALS["xoopsDB"]->quote($cryptiopass))));
		return $result;
	}
}


if (!function_exists("pleaseCompressData")) {
	/**
	 * Compresses a textualisation
	 *
	 * @param string $data
	 * @return string:
	 */
	function pleaseCompressData($data = '')
	{
		$sql = "SELECT COMPRESS(%s) as `compressed`";
		list($result) = $GLOBALS["xoopsDB"]->fetchRow($GLOBALS["xoopsDB"]->queryF(sprintf($sql, $GLOBALS["xoopsDB"]->quote($data))));
		return $result;
	}
}


if (!function_exists("pleaseDecompressData")) {
	/**
	 * Compresses a textualisation
	 *
	 * @param string $data
	 * @return string:
	 */
	function pleaseDecompressData($data = '')
	{
		$sql = "SELECT DECOMPRESS(%s) as `compressed`";
		list($result) = $GLOBALS["xoopsDB"]->fetchRow($GLOBALS["xoopsDB"]->queryF(sprintf($sql, $GLOBALS["xoopsDB"]->quote($data))));
		return $result;
	}
}


if (!function_exists("MakePHPFont")) {
	/**
	 * Function for making PHP font for TCPDF and similar applications
	 * 
	 * @param string $fontfile path to font file (TTF, OTF or PFB).
	 * @param string $fmfile font metrics file (UFM or AFM).
	 * @param boolean $embedded Set to false to not embed the font, true otherwise (default).
	 * @param string $enc Name of the encoding table to use. Omit this parameter for TrueType Unicode, OpenType Unicode and symbolic fonts like Symbol or ZapfDingBats.
	 * @param array $patch Optional modification of the encoding
	 */
	function MakePHPFont($fontfile, $fmfile, $path = "/tmp/", $embedded=true, $enc='cp1252', $patch=array()) {
		//Generate a font definition file
		ini_set('auto_detect_line_endings', '1');
		if (!file_exists($fontfile)) {
			die('Error: file not found: '.$fontfile);
		}
		if (!file_exists($fmfile)) {
			die('Error: file not found: '.$fmfile);
		}
		$cidtogidmap = '';
		$map = array();
		$diff = '';
		$dw = 0; // default width
		$ffext = strtolower(substr($fontfile, -3));
		$fmext = strtolower(substr($fmfile, -3));
		if ($fmext == 'afm') {
			if (($ffext == 'ttf') OR ($ffext == 'otf')) {
				$type = 'TrueType';
			} elseif ($ffext == 'pfb') {
				$type = 'Type1';
			} else {
				die('Error: unrecognized font file extension: '.$ffext);
			}
			if ($enc) {
				$map = ReadMap($enc);
				foreach ($patch as $cc => $gn) {
					$map[$cc] = $gn;
				}
			}
			$fm = ReadAFM($fmfile, $map);
			if (isset($widths['.notdef'])) {
				$dw = $widths['.notdef'];
			}
			if ($enc) {
				$diff = MakeFontEncoding($map);
			}
			$fd = MakeFontDescriptor($fm, empty($map));
		} elseif ($fmext == 'ufm') {
			$enc = '';
			if (($ffext == 'ttf') OR ($ffext == 'otf')) {
				$type = 'TrueTypeUnicode';
			} else {
				die('Error: not a TrueType font: '.$ffext);
			}
			$fm = ReadUFM($fmfile, $cidtogidmap);
			$dw = $fm['MissingWidth'];
			$fd = MakeFontDescriptor($fm, false);
		}
		//Start generation
		$s = '<?php'."\n";
		$s .= '$type=\''.$type."';\n";
		$s .= '$name=\''.$fm['FontName']."';\n";
		$s .= '$desc='.$fd.";\n";
		if (!isset($fm['UnderlinePosition'])) {
			$fm['UnderlinePosition'] = -100;
		}
		if (!isset($fm['UnderlineThickness'])) {
			$fm['UnderlineThickness'] = 50;
		}
		$s .= '$up='.$fm['UnderlinePosition'].";\n";
		$s .= '$ut='.$fm['UnderlineThickness'].";\n";
		if ($dw <= 0) {
			if (isset($fm['Widths'][32]) AND ($fm['Widths'][32] > 0)) {
				// assign default space width
				$dw = $fm['Widths'][32];
			} else {
				$dw = 600;
			}
		}
		$s .= '$dw='.$dw.";\n";
		$w = MakeWidthArray($fm);
		$s .= '$cw='.$w.";\n";
		$s .= '$enc=\''.$enc."';\n";
		$s .= '$diff=\''.$diff."';\n";
		$basename = substr(basename($fmfile), 0, -4);
		if ($embedded) {
			//Embedded font
			$f = fopen($fontfile,'rb');
			if (!$f) {
				die('Error: Unable to open '.$fontfile);
			}
			$file = fread($f, filesize($fontfile));
			fclose($f);
			if ($type == 'Type1') {
				//Find first two sections and discard third one
				$header = (ord($file{0}) == 128);
				if ($header) {
					//Strip first binary header
					$file = substr($file, 6);
				}
				$pos = strpos($file, 'eexec');
				if (!$pos) {
					die('Error: font file does not seem to be valid Type1');
				}
				$size1 = $pos + 6;
				if ($header AND (ord($file{$size1}) == 128)) {
					//Strip second binary header
					$file = substr($file, 0, $size1).substr($file, $size1+6);
				}
				$pos = strpos($file, '00000000');
				if (!$pos) {
					die('Error: font file does not seem to be valid Type1');
				}
				$size2 = $pos - $size1;
				$file = substr($file, 0, ($size1 + $size2));
			}
			$basename = strtolower($basename);
			
			$cmp = $basename.'.z';
			SaveToFile($cmp, gzcompress($file, 9), 'b');
			$s .= "$file=__DIR__.DIRECTORY_SEPARATOR.'$cmp';\n";
			//print "Font file compressed (".$cmp.")\n";
			if (!empty($cidtogidmap)) {
				$cmp = $basename.'.ctg.z';
				SaveToFile($cmp, gzcompress($cidtogidmap, 9), 'b');
				//print "CIDToGIDMap created and compressed (".$cmp.")\n";
				$s .= '$ctg=\''.$cmp."';\n";
			}
			
			if($type == 'Type1') {
				$s .= '$size1='.$size1.";\n";
				$s .= '$size2='.$size2.";\n";
			} else {
				$s.='$originalsize='.filesize($fontfile).";\n";
			}
		} else {
			//Not embedded font
			$s .= '$file='."'';\n";
		}
		$s .= "?>";
		SaveToFile($path . DIRECTORY_SEPARATOR . $basename.'.php',$s);
		//print "Font definition file generated (".$basename.".php)\n";
	}
}

if (!function_exists("ReadMap")) {
	/**
	 * Read the specified encoding map.
	 * @param string $enc map name (see /enc/ folder for valid names).
	 */
	function ReadMap($enc) {
		//Read a map file
		$file = __DIR__.'/data/enc/'.strtolower($enc).'.map';
		$a = file($file);
		if (empty($a)) {
			die('Error: encoding not found: '.$enc);
		}
		$cc2gn = array();
		foreach ($a as $l) {
			if ($l{0} == '!') {
				$e = preg_split('/[ \\t]+/',rtrim($l));
				$cc = hexdec(substr($e[0],1));
				$gn = $e[2];
				$cc2gn[$cc] = $gn;
			}
		}
		for($i = 0; $i <= 255; $i++) {
			if(!isset($cc2gn[$i])) {
				$cc2gn[$i] = '.notdef';
			}
		}
		return $cc2gn;
	}
}

if (!function_exists("ReadUFM")) {
	/**
	 * Read UFM file
	 * 
	 * @param $file string
	 * @param $cidtogidmap array
	 */
	function ReadUFM($file, &$cidtogidmap) {
		//Prepare empty CIDToGIDMap
		$cidtogidmap = str_pad('', (256 * 256 * 2), "\x00");
		//Read a font metric file
		$a = file($file);
		if (empty($a)) {
			die('File not found');
		}
		$widths = array();
		$fm = array();
		foreach($a as $l) {
			$e = explode(' ',chop($l));
			if(count($e) < 2) {
				continue;
			}
			$code = $e[0];
			$param = $e[1];
			if($code == 'U') {
				// U 827 ; WX 0 ; N squaresubnosp ; G 675 ;
				//Character metrics
				$cc = (int)$e[1];
				if ($cc != -1) {
					$gn = $e[7];
					$w = $e[4];
					$glyph = $e[10];
					$widths[$cc] = $w;
					if($cc == ord('X')) {
						$fm['CapXHeight'] = $e[13];
					}
					// Set GID
					if (($cc >= 0) AND ($cc < 0xFFFF) AND $glyph) {
						$cidtogidmap{($cc * 2)} = chr($glyph >> 8);
						$cidtogidmap{(($cc * 2) + 1)} = chr($glyph & 0xFF);
					}
				}
				if(($gn == '.notdef') AND (!isset($fm['MissingWidth']))) {
					$fm['MissingWidth'] = $w;
				}
			} elseif($code == 'FontName') {
				$fm['FontName'] = $param;
			} elseif($code == 'Weight') {
				$fm['Weight'] = $param;
			} elseif($code == 'ItalicAngle') {
				$fm['ItalicAngle'] = (double)$param;
			} elseif($code == 'Ascender') {
				$fm['Ascender'] = (int)$param;
			} elseif($code == 'Descender') {
				$fm['Descender'] = (int)$param;
			} elseif($code == 'UnderlineThickness') {
				$fm['UnderlineThickness'] = (int)$param;
			} elseif($code == 'UnderlinePosition') {
				$fm['UnderlinePosition'] = (int)$param;
			} elseif($code == 'IsFixedPitch') {
				$fm['IsFixedPitch'] = ($param == 'true');
			} elseif($code == 'FontBBox') {
				$fm['FontBBox'] = array($e[1], $e[2], $e[3], $e[4]);
			} elseif($code == 'CapHeight') {
				$fm['CapHeight'] = (int)$param;
			} elseif($code == 'StdVW') {
				$fm['StdVW'] = (int)$param;
			}
		}
		if(!isset($fm['MissingWidth'])) {
			$fm['MissingWidth'] = 600;
		}
		if(!isset($fm['FontName'])) {
			die('FontName not found');
		}
		$fm['Widths'] = $widths;
		return $fm;
	}
}

if (!function_exists("ReadAFM")) {
	/**
	 * Read AFM file
	 * 
	 * @param $file string
	 * @param $map array
	 */
	function ReadAFM($file,&$map) {
		//Read a font metric file
		$a = file($file);
		if(empty($a)) {
			die('File not found');
		}
		$widths = array();
		$fm = array();
		$fix = array(
				'Edot'=>'Edotaccent',
				'edot'=>'edotaccent',
				'Idot'=>'Idotaccent',
				'Zdot'=>'Zdotaccent',
				'zdot'=>'zdotaccent',
				'Odblacute' => 'Ohungarumlaut',
				'odblacute' => 'ohungarumlaut',
				'Udblacute'=>'Uhungarumlaut',
				'udblacute'=>'uhungarumlaut',
				'Gcedilla'=>'Gcommaaccent'
				,'gcedilla'=>'gcommaaccent',
				'Kcedilla'=>'Kcommaaccent',
				'kcedilla'=>'kcommaaccent',
				'Lcedilla'=>'Lcommaaccent',
				'lcedilla'=>'lcommaaccent',
				'Ncedilla'=>'Ncommaaccent',
				'ncedilla'=>'ncommaaccent',
				'Rcedilla'=>'Rcommaaccent',
				'rcedilla'=>'rcommaaccent',
				'Scedilla'=>'Scommaaccent',
				'scedilla'=>'scommaaccent',
				'Tcedilla'=>'Tcommaaccent',
				'tcedilla'=>'tcommaaccent',
				'Dslash'=>'Dcroat',
				'dslash'=>'dcroat',
				'Dmacron'=>'Dcroat',
				'dmacron'=>'dcroat',
				'combininggraveaccent'=>'gravecomb',
				'combininghookabove'=>'hookabovecomb',
				'combiningtildeaccent'=>'tildecomb',
				'combiningacuteaccent'=>'acutecomb',
				'combiningdotbelow'=>'dotbelowcomb',
				'dongsign'=>'dong'
		);
		foreach($a as $l) {
			$e = explode(' ', rtrim($l));
			if (count($e) < 2) {
				continue;
			}
			$code = $e[0];
			$param = $e[1];
			if ($code == 'C') {
				//Character metrics
				$cc = (int)$e[1];
				$w = $e[4];
				$gn = $e[7];
				if (substr($gn, -4) == '20AC') {
					$gn = 'Euro';
				}
				if (isset($fix[$gn])) {
					//Fix incorrect glyph name
					foreach ($map as $c => $n) {
						if ($n == $fix[$gn]) {
							$map[$c] = $gn;
						}
					}
				}
				if (empty($map)) {
					//Symbolic font: use built-in encoding
					$widths[$cc] = $w;
				} else {
					$widths[$gn] = $w;
					if($gn == 'X') {
						$fm['CapXHeight'] = $e[13];
					}
				}
				if($gn == '.notdef') {
					$fm['MissingWidth'] = $w;
				}
			} elseif($code == 'FontName') {
				$fm['FontName'] = $param;
			} elseif($code == 'Weight') {
				$fm['Weight'] = $param;
			} elseif($code == 'ItalicAngle') {
				$fm['ItalicAngle'] = (double)$param;
			} elseif($code == 'Ascender') {
				$fm['Ascender'] = (int)$param;
			} elseif($code == 'Descender') {
				$fm['Descender'] = (int)$param;
			} elseif($code == 'UnderlineThickness') {
				$fm['UnderlineThickness'] = (int)$param;
			} elseif($code == 'UnderlinePosition') {
				$fm['UnderlinePosition'] = (int)$param;
			} elseif($code == 'IsFixedPitch') {
				$fm['IsFixedPitch'] = ($param == 'true');
			} elseif($code == 'FontBBox') {
				$fm['FontBBox'] = array($e[1], $e[2], $e[3], $e[4]);
			} elseif($code == 'CapHeight') {
				$fm['CapHeight'] = (int)$param;
			} elseif($code == 'StdVW') {
				$fm['StdVW'] = (int)$param;
			}
		}
		if (!isset($fm['FontName'])) {
			die('FontName not found');
		}
		if (!empty($map)) {
			if (!isset($widths['.notdef'])) {
				$widths['.notdef'] = 600;
			}
			if (!isset($widths['Delta']) AND isset($widths['increment'])) {
				$widths['Delta'] = $widths['increment'];
			}
			//Order widths according to map
			for ($i = 0; $i <= 255; $i++) {
				if (!isset($widths[$map[$i]])) {
					//print "Warning: character ".$map[$i]." is missing\n";
					$widths[$i] = $widths['.notdef'];
				} else {
					$widths[$i] = $widths[$map[$i]];
				}
			}
		}
		$fm['Widths'] = $widths;
		return $fm;
	}
}

if (!function_exists("MakeFontDescriptor")) {
	/**
	 * Makes font description header
	 * 
	 * @param $fm array
	 * @param $symbolic boolean
	 */
	function MakeFontDescriptor($fm, $symbolic=false) {
		//Ascent
		$asc = (isset($fm['Ascender']) ? $fm['Ascender'] : 1000);
		$fd = "array('Ascent'=>".$asc;
		//Descent
		$desc = (isset($fm['Descender']) ? $fm['Descender'] : -200);
		$fd .= ",'Descent'=>".$desc;
		//CapHeight
		if (isset($fm['CapHeight'])) {
			$ch = $fm['CapHeight'];
		} elseif (isset($fm['CapXHeight'])) {
			$ch = $fm['CapXHeight'];
		} else {
			$ch = $asc;
		}
		$fd .= ",'CapHeight'=>".$ch;
		//Flags
		$flags = 0;
		if (isset($fm['IsFixedPitch']) AND $fm['IsFixedPitch']) {
			$flags += 1<<0;
		}
		if ($symbolic) {
			$flags += 1<<2;
		} else {
			$flags += 1<<5;
		}
		if (isset($fm['ItalicAngle']) AND ($fm['ItalicAngle'] != 0)) {
			$flags += 1<<6;
		}
		$fd .= ",'Flags'=>".$flags;
		//FontBBox
		if (isset($fm['FontBBox'])) {
			$fbb = $fm['FontBBox'];
		} else {
			$fbb = array(0, ($desc - 100), 1000, ($asc + 100));
		}
		$fd .= ",'FontBBox'=>'[".$fbb[0].' '.$fbb[1].' '.$fbb[2].' '.$fbb[3]."]'";
		//ItalicAngle
		$ia = (isset($fm['ItalicAngle']) ? $fm['ItalicAngle'] : 0);
		$fd .= ",'ItalicAngle'=>".$ia;
		//StemV
		if (isset($fm['StdVW'])) {
			$stemv = $fm['StdVW'];
		} elseif (isset($fm['Weight']) && preg_match('(bold|black)', $fm['Weight'])) {
			$stemv = 120;
		} else {
			$stemv = 70;
		}
		$fd .= ",'StemV'=>".$stemv;
		//MissingWidth
		if(isset($fm['MissingWidth'])) {
			$fd .= ",'MissingWidth'=>".$fm['MissingWidth'];
		}
		$fd .= ')';
		return $fd;
	}
}

if (!function_exists("MakeWidthArray")) {
	/**
	 * Makes Widths Array for Font
	 * 
	 * @param array $fm
	 */
	function MakeWidthArray($fm) {
		//Make character width array
		$s = 'array(';
		$cw = $fm['Widths'];
		$els = array();
		$c = 0;
		foreach ($cw as $i => $w) {
			if (is_numeric($i)) {
				$els[] = (((($c++)%10) == 0) ? "\n" : '').$i.'=>'.$w;
			}
		}
		$s .= implode(',', $els);
		$s .= ')';
		return $s;
	}
}

if (!function_exists("MakeFontEncoding")) {
	/**
	 * Makes a Font Encoding Mapping References
	 * 
	 * @param array $map
	 */
	function MakeFontEncoding($map) {
		//Build differences from reference encoding
		$ref = ReadMap('cp1252');
		$s = '';
		$last = 0;
		for ($i = 32; $i <= 255; $i++) {
			if ($map[$i] != $ref[$i]) {
				if ($i != $last+1) {
					$s .= $i.' ';
				}
				$last = $i;
				$s .= '/'.$map[$i].' ';
			}
		}
		return rtrim($s);
	}
}

if (!function_exists("SaveToFile")) {
	/**
	 * Writes a file to the filebase
	 * 
	 * @param string $file
	 * @param string $s
	 * @param string $mode
	 */
	function SaveToFile($file, $s, $mode='t') {
		$f = fopen($file, 'w'.$mode);
		if(!$f) {
			die('Can\'t write to file '.$file);
		}
		fwrite($f, $s, strlen($s));
		fclose($f);
	}
}

if (!function_exists("ReadShort")) {
	/**
	 * Read's Short Data from File Via Unpack
	 * 
	 * @param string $f
	 */
	function ReadShort($f) {
		$a = unpack('n1n', fread($f, 2));
		return $a['n'];
	}
}

if (!function_exists("ReadLong")) {
	/**
	 * Reads Long Data from File
	 * 
	 * @param string $f
	 */
	function ReadLong($f) {
		$a = unpack('N1N', fread($f, 4));
		return $a['N'];
	}
}

if (!function_exists("putRawFile")) {
	/**
	 * Saves a Raw File to the Filebase
	 * 
	 * @param string $file
	 * @param string $data
	 * 
	 * @return boolean
	 */
	function putRawFile($file = '', $data = '')
	{
		$lineBreak = "\n";
		if (substr(PHP_OS, 0, 3) == 'WIN') {
			$lineBreak = "\r\n";
		}
		if (!is_dir(dirname($file)))
			if (strpos(' '.$file, _MD_CONVERT_PATH_CACHE))
				mkdirSecure(dirname($file), 0777, true);
			else
				mkdir(dirname($file), 0777, true);
		elseif (strpos(' '.$file, _MD_CONVERT_PATH_CACHE) && !file_exists(_MD_CONVERT_PATH_CACHE . DIRECTORY_SEPARATOR . '.htaccess'))
			SaveToFile(_MD_CONVERT_PATH_CACHE . DIRECTORY_SEPARATOR . '.htaccess', "<Files ~ \"^.*$\">\n\tdeny from all\n</Files>");
		if (is_file($file))
			unlink($file);
		return SaveToFile($file, $data);
	}
}


if (!function_exists("getFileDIZ")) {
	/**
	 * Generates the DIZ file template from the template in /data
	 * 
	 * @param integer $font_id
	 * @param integer $upload_id
	 * @param string $fingerprint
	 * @param string $filename
	 * @param integer $bytes
	 * @param array $filez
	 * 
	 * @return string
	 */
	
}

if (!function_exists("getHTMLForm")) {
	/**
	 * Get the HTML Forms for the API
	 * 
	 * @param unknown_type $mode
	 * 
	 * @return string
	 */
	function getHTMLForm($mode = 'uploads')
	{
		xoops_loadLanguage('modinfo', _MD_CONVERT_MODULE_DIRNAME);
		$ua = substr(sha1($_SERVER['HTTP_USER_AGENT']), mt_rand(0,32), 9);
		xoops_load('XoopsSecurity');
		$xoopsSecurity = new XoopsSecurity();
		$token = $xoopsSecurity->createToken(1800, 'XOOPS_TOKEN');
		$form = array();
		switch ($mode)
		{
			case "uploads":
				if ($GLOBALS['convertConfigsList']['htaccess'])
					$url = XOOPS_URL . '/' . $GLOBALS['convertConfigsList']['base'] . '/upload.html';
				else 
					$url = XOOPS_URL . '/modules/' . _MD_CONVERT_MODULE_DIRNAME . '/upload.php';
				$form[] = "<form name=\"" . $ua . "\" method=\"POST\" enctype=\"multipart/form-data\" action=\"" . $url . "\">";
				$form[] = "\t<table class='font-uploader' id='font-uploader' style='vertical-align: top !important; min-width: 98%;'>";
				$form[] = "\t\t<tr style='vertical-align: middle !important;'>";
				$form[] = "\t\t\t<td style='width: 320px;'>";
				$form[] = "\t\t\t\t<label for='email' style='color: rgb(0,0,0); font-size: 99%;'>Email:&nbsp;<font style='color: rgb(250,0,0); font-size: 109%; font-weight: bold'>*</font></label>";
				$form[] = "\t\t\t</td>";
				$form[] = "\t\t\t<td>";
				$form[] = "\t\t\t\t<input type='textbox' name='email' id='email' maxlen='198' size='41' />&nbsp;&nbsp;";
				$form[] = "\t\t\t</td>";
				$form[] = "\t\t\t<td>&nbsp;</td>";
				$form[] = "\t\t</tr>";
				$form[] = "\t\t<tr style='vertical-align: middle !important;'>";
				$form[] = "\t\t\t<td style='width: 320px;'>";
				$form[] = "\t\t\t\t<label for='name' style='color: rgb(0,0,0); font-size: 99%;' >Full Name:&nbsp;<font style='color: rgb(250,0,0); font-size: 109%; font-weight: bold'>*</font></label>";
				$form[] = "\t\t\t</td>";
				$form[] = "\t\t\t<td>";
				$form[] = "\t\t\t\t<input type='textbox' name='name' id='name' maxlen='198' size='41' /><br/>";
				$form[] = "\t\t\t</td>";
				$form[] = "\t\t\t<td>&nbsp;</td>";
				$form[] = "\t\t</tr>";
				$form[] = "\t\t<tr style='vertical-align: middle !important;'>";
				$form[] = "\t\t\t<td>";
				$form[] = "\t\t\t\t<label for='bizo' style='color: rgb(0,0,0); font-size: 99%;'>Organisation:&nbsp;<font style='color: rgb(250,0,0); font-size: 109%; font-weight: bold'>*</font></label>";
				$form[] = "\t\t\t</td>";
				$form[] = "\t\t\t<td style='width: 320px;'>";
				$form[] = "\t\t\t\t<input type='textbox' name='bizo' id='bizo' maxlen='198' size='41' value='" . constant("_MD_CONVERT_DEFAULT_COMPANY") . "'/><br/>";
				$form[] = "\t\t\t</td>";
				$form[] = "\t\t\t<td>&nbsp;</td>";
				$form[] = "\t\t</tr>";
				$form[] = "\t\t<tr style='vertical-align: middle !important;'>";
				$form[] = "\t\t\t<td style='width: 320px;'>";
				$form[] = "\t\t\t\t<label for='prefix' style='color: rgb(0,0,0); font-size: 99%;'>Twitter User Via:&nbsp;<font style='color: rgb(250,0,0); font-size: 109%; font-weight: bold'>*</font></label>";
				$form[] = "\t\t\t</td>";
				$form[] = "\t\t\t<td>";
				$form[] = "\t\t\t\t<input type='textbox' name='twitter' id='twitter' maxlen='42' size='23' value='" . constant("_MD_CONVERT_DEFAULT_TWITTER") . "' />&nbsp;&nbsp;";
				$form[] = "\t\t\t</td>";
				$form[] = "\t\t\t<td>";
				$form[] = "\t\t\t\t&nbsp;";
				$form[] = "\t\t\t</td>";
				$form[] = "\t\t</tr>";
				$form[] = "\t\t<tr style='vertical-align: middle !important;'>";
				$form[] = "\t\t\t<td colspan='3'>";
				$form[] = "\t\t\t\t<label for='".$ua."' style='color: rgb(0,0,0); font-size: 87%; font-weight: bold; text-align: left !important;'>Font file to upload:&nbsp;<font style='color: rgb(250,0,0); font-size: 139%; font-weight: bold'>*</font></label>";
				$form[] = "\t\t\t\t<input type='file' name='" . $ua . "' id='" . $ua ."'><br/>";
				$form[] = "\t\t\t\t<div style='margin-left:42px; font-size: 71.99%; margin-top: 7px; padding: 11px;'>";
				$form[] = "\t\t\t\t\t ~~ <strong>Maximum Upload Size Is: <em style='color:rgb(255,100,123); font-weight: bold; font-size: 132.6502%;'>" . ini_get('upload_max_filesize') . "!!!</em></strong><br/>";
				$form[] = "\t\t\t\t\t ~~ <strong>Font File Formats Supported: <em style='color:rgb(15,70 43); font-weight: bold; font-size: 81.6502%;'>*." . str_replace("\n" , "", implode(" *.", array_unique(getFontExtensions()))) . "</em></strong>!<br/>";
				$form[] = "\t\t\t\t</div>";
				$form[] = "\t\t\t</td>";
				$form[] = "\t\t</tr>";
				$form[] = "\t\t<tr>";
				$form[] = "\t\t\t<td colspan='3' style='padding-left:64px;'>";
				$form[] = "\t\t\t\t<input type='hidden' name='XOOPS_TOKEN' value='" . $token ."'>";
				$form[] = "\t\t\t\t<input type='submit' value='Upload File' name='submit' style='padding:11px; font-size:122%;'>";
				$form[] = "\t\t\t</td>";
				$form[] = "\t\t</tr>";
				$form[] = "\t\t<tr>";
				$form[] = "\t\t\t<td colspan='3' style='padding-top: 8px; padding-bottom: 14px; padding-right:35px; text-align: right;'>";
				$form[] = "\t\t\t\t<font style='color: rgb(250,0,0); font-size: 139%; font-weight: bold;'>* </font><font  style='color: rgb(10,10,10); font-size: 99%; font-weight: bold'><em style='font-size: 76%'>~ Required Field for Form Submission</em></font>";
				$form[] = "\t\t\t</td>";
				$form[] = "\t\t</tr>";
				$form[] = "\t\t<tr>";
				$form[] = "\t</table>";
				$form[] = "</form>";
				break;
		}
		return implode("\n", $form);
	}
}

if (!function_exists("whitelistGetIP")) {
	/**
	 * Provides an associative array of whitelisted IP Addresses
	 *
	 * @return array
 	 */
	function whitelistGetIPAddy() {
		return array_merge(whitelistGetNetBIOSIP(), file(dirname(dirname(dirname(dirname(__FILE__)))) . DIRECTORY_SEPARATOR . 'whitelist.txt'));
	}
}

if (!function_exists("whitelistGetNetBIOSIP")) {
	/**
	 * provides an associative array of whitelisted IP Addresses base on TLD and NetBIOS Addresses
	 *
	 * @return array
 	 */
	function whitelistGetNetBIOSIP() {
		$ret = array();
		foreach(file(dirname(dirname(dirname(dirname(__FILE__)))) . DIRECTORY_SEPARATOR . 'whitelist-domains.txt') as $domain) {
			$ip = gethostbyname($domain);
			$ret[$ip] = $ip;
		}
		return $ret;
	}
}

if (!function_exists("whitelistGetIP")) {
	/**
	 * get the True IPv4/IPv6 address of the client using the API
	 * 
	 * @param boolean $asString
	 *
	 * @return mixed
	 */
	function whitelistGetIP($asString = true){
		
		// Gets the proxy ip sent by the user
		$proxy_ip = '';
		if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$proxy_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
			} else
				if (!empty($_SERVER['HTTP_X_FORWARDED'])) {
					$proxy_ip = $_SERVER['HTTP_X_FORWARDED'];
					} else
						if (! empty($_SERVER['HTTP_FORWARDED_FOR'])) {
							$proxy_ip = $_SERVER['HTTP_FORWARDED_FOR'];
							} else
								if (!empty($_SERVER['HTTP_FORWARDED'])) {
									$proxy_ip = $_SERVER['HTTP_FORWARDED'];
									} else
										if (!empty($_SERVER['HTTP_VIA'])) {
											$proxy_ip = $_SERVER['HTTP_VIA'];
										} else
											if (!empty($_SERVER['HTTP_X_COMING_FROM'])) {
												$proxy_ip = $_SERVER['HTTP_X_COMING_FROM'];
												} else
													if (!empty($_SERVER['HTTP_COMING_FROM'])) {
														$proxy_ip = $_SERVER['HTTP_COMING_FROM'];
		}
												
		if (!empty($proxy_ip) && $is_ip = preg_match('/^([0-9]{1,3}.){3,3}[0-9]{1,3}/', $proxy_ip, $regs) && count($regs) > 0)  {
			$the_IP = $regs[0];
		} else {
			$the_IP = $_SERVER['REMOTE_ADDR'];
		}
		
		if (isset($_REQUEST['ip']) && !empty($_REQUEST['ip']) && $is_ip = preg_match('/^([0-9]{1,3}.){3,3}[0-9]{1,3}/', $_REQUEST['ip'], $regs) && count($regs) > 0)  {
			$ip = $regs[0];
		}
			
		return isset($ip) && !empty($ip)?(($asString) ? $ip : ip2long($ip)):(($asString) ? $the_IP : ip2long($the_IP));
	}
}


if (!function_exists("getFontExtensions")) {
	/**
	 * Get the mime type for a file extension
	 *
	 * @param string $extension
	 *
	 * @return string
	 */
	function getFontExtensions()
	{
		$result = explode("\n", file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'font-extensions.diz'));
		sort($result);
		return $result;
	}
}


if (!function_exists("getFontFormats")) {
	/**
	 * Get the mime type for a file extension
	 *
	 * @param string $extension
	 *
	 * @return string
	 */
	function getFontFormats()
	{
		$result = explode("\n", file_get_contents($file = __DIR__ . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'font-formats.diz'));
		sort($result);
		return $result;
	}
}

if (!function_exists("getMimetype")) {
	/**
	 * Get the mime type for a file extension
	 *
	 * @param string $extension
	 *
	 * @return string
	 */
	function getMimetype($extension = '-=-')
	{
		$mimetypes = cleanWhitespaces(file(__DIR__ . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'mimetypes.diz'));
		foreach($mimetypes as $mimetype)
		{
			$parts = explode("||", $mimetype);
			if (strtolower($extension) == strtolower($parts[0]))
				return $parts[1];
			if (strtolower("-=-") == strtolower($parts[0]))
				$final = $parts[1];
		}
		return $final;
	}
}

if (!function_exists("mkdirSecure")) {
	/**
	 * Make a folder and secure's it with .htaccess mod-rewrite with apache2
	 *
	 * @param string $path
	 * @param integer $perm
	 * @param boolean $secure
	 *
	 * @return boolean
	 */
	function mkdirSecure($path = '', $perm = 0777, $secure = true)
	{
		if (!is_dir($path))
		{
			mkdir($path, $perm, true);
			if ($secure == true)
			{
				SaveToFile($path . DIRECTORY_SEPARATOR . '.htaccess', "<Files ~ \"^.*$\">\n\tdeny from all\n</Files>");
			}
			return true;
		}
		return false;
	}
}

if (!function_exists("cleanWhitespaces")) {
	/**
	 * Clean's an array of \n, \r, \t when importing for example with file() and includes carriage returns in array
	 *
	 * @param array $array
	 *
	 * @return array
	 */
	function cleanWhitespaces($array = array())
	{
		foreach($array as $key => $value)
		{
			if (is_array($value))
				$array[$key] = cleanWhitespaces($value);
			else {
				$array[$key] = trim(str_replace(array("\n", "\r", "\t"), "", $value));
			}
		}
		return $array;
	}
}

if (!function_exists("getURIData")) {
	/**
	 * uses cURL to return data from the URL/URI with POST Data if required
	 *
	 * @param string $urt
	 * @param integer $timeout
	 * @param integer $connectout
	 * @param array $post_data
	 *
	 * @return string
	 */
	function getURIData($uri = '', $timeout = 65, $connectout = 65, $post_data = array())
	{
		if (!function_exists("curl_init"))
		{
			die("Need to install php-curl: $ sudo apt-get install php-curl");
		}
		if (!$btt = curl_init($uri)) {
			return false;
		}
		curl_setopt($btt, CURLOPT_HEADER, 0);
		curl_setopt($btt, CURLOPT_POST, (count($post_data)==0?false:true));
		if (count($post_data)!=0)
			curl_setopt($btt, CURLOPT_POSTFIELDS, http_build_query($post_data));
		curl_setopt($btt, CURLOPT_CONNECTTIMEOUT, $connectout);
		curl_setopt($btt, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($btt, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($btt, CURLOPT_VERBOSE, false);
		curl_setopt($btt, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($btt, CURLOPT_SSL_VERIFYPEER, false);
		$data = curl_exec($btt);
		curl_close($btt);
		return $data;
	}
}

if (!function_exists("getArchivedZIPFile")) {
	/**
	 * get a file from a zip archive based in files
	 *
	 * @return string
	 */
	function getArchivedZIPFile($zip_resource = '', $zip_file = '')
	{
		$data = '';
 		$zip = zip_open($zip_resource);
        if ($zip) {
        	while ($zip_entry = zip_read($zip)) {
            	if (substr(strtolower($entry = zip_entry_name($zip_entry)), strlen(zip_entry_name($zip_entry)) - strlen($zip_file), strlen($zip_file))==strtolower($zip_file))
            	{
                	if (zip_entry_open($zip, $zip_entry, "r")) {
                		$GLOBALS['filename'] = zip_entry_name($zip_entry);
                    	$data = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
                        zip_entry_close($zip_entry);
                        continue;
                        continue;
                    }
            	}
        	}
            zip_close($zip);
         }
         return $data;
		
	}
}

if (!function_exists('sef'))
{
	/**
	 * Safe encoded paths elements
	 *
	 * @param unknown $datab
	 * @param string $char
	 * 
	 * @return string
	 */
	function sef($value = '', $stripe ='-')
	{
		return(strtolower(getOnlyAlpha($value, $stripe)));
	}
}


if (!function_exists('getOnlyAlpha'))
{
	/**
	 * Safe encoded paths elements
	 *
	 * @param unknown $datab
	 * @param string $char
	 * 
	 * @return string
	 */
	function getOnlyAlpha($value = '', $stripe ='-')
	{
		$value = str_replace('&', 'and', $value);
		$value = str_replace(array("'", '"', "`"), 'tick', $value);
		$replacement_chars = array();
		$accepted = array("a","b","c","d","e","f","g","h","i","j","k","l","m","n","m","o","p","q",
				"r","s","t","u","v","w","x","y","z","0","9","8","7","6","5","4","3","2","1");
		for($i=0;$i<256;$i++){
			if (!in_array(strtolower(chr($i)),$accepted))
				$replacement_chars[] = chr($i);
		}
		$result = trim(str_replace($replacement_chars, $stripe, ($value)));
		while(strpos($result, $stripe.$stripe, 0))
			$result = (str_replace($stripe.$stripe, $stripe, $result));
		while(substr($result, 0, strlen($stripe)) == $stripe)
			$result = substr($result, strlen($stripe), strlen($result) - strlen($stripe));
		while(substr($result, strlen($result) - strlen($stripe), strlen($stripe)) == $stripe)
			$result = substr($result, 0, strlen($result) - strlen($stripe));
		return($result);
	}
}

if (!function_exists("spacerName")) {
	/**
	 * Formats font name to correct definition textualisation without typed precisioning
	 *
	 * @param string $name
	 * 
	 * @return string
	 */
	function spacerName($name = '')
	{
		$name = getOnlyAlpha(str_replace(array('-', ':', ',', '<', '>', ';', '+', '_', '(', ')', '[', ']', '{', '}', '='), ' ', $name), ' ');
		$nname = '';
		$previous = $last = '';
		for($i=0; $i<strlen($name); $i++)
		{
			if (substr($name, $i, 1)==strtoupper(substr($name, $i, 1)) && $last==strtolower($last))
			{
				$nname .= ' ' . substr($name, $i, 1); 
			} else 
				$nname .= substr($name, $i, 1);
			$last=substr($name, $i, 1);
		}
		while(strpos($nname, '  ')>0)
			$nname = str_replace('  ', ' ', $nname);
		return trim(implode(' ', array_unique(explode(' ', $nname))));
	}
}

if (!function_exists("checkEmail")) {
	/**
	 * checks if a data element is an email address
	 *
	 * @param mixed $email
	 * 
	 * @return bool|mixed
	 */
	function checkEmail($email)
	{
		if (!$email || !preg_match('/^[^@]{1,64}@[^@]{1,255}$/', $email)) {
			return false;
		}
		$email_array = explode("@", $email);
		$local_array = explode(".", $email_array[0]);
		for ($i = 0; $i < sizeof($local_array); $i++) {
			if (!preg_match("/^(([A-Za-z0-9!#$%&'*+\/\=?^_`{|}~-][A-Za-z0-9!#$%&'*+\/\=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$/", $local_array[$i])) {
				return false;
			}
		}
		if (!preg_match("/^\[?[0-9\.]+\]?$/", $email_array[1])) {
			$domain_array = explode(".", $email_array[1]);
			if (sizeof($domain_array) < 2) {
				return false; // Not enough parts to domain
			}
			for ($i = 0; $i < sizeof($domain_array); $i++) {
				if (!preg_match("/^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]+))$/", $domain_array[$i])) {
					return false;
				}
			}
		}
		return $email;
	}
}

if (!function_exists("writeRawFile")) {
	/**
	 * Writes RAW File Data
	 *
	 * @param string $file
	 * @param string $data
	 *
	 * @return boolean
	 */
	function writeRawFile($file = '', $data = '')
	{
		if (!is_dir(_MD_CONVERT_PATH_CACHE))
			mkdir(_MD_CONVERT_PATH_CACHE, 0777, true);
		if (!is_dir(dirname($file)))
			mkdir(dirname($file), 0777, true);
		if (is_file($file))
			unlink($file);
		SaveToFile($file, $data);
		if (!strpos($file, 'caches-files-sessioning.json') && strpos($file, '.json'))
		{
			
			if (file_exists(_MD_CONVERT_PATH_CACHE . DIRECTORY_SEPARATOR . 'caches-files-sessioning.json'))
				$sessions = json_decode(file_get_contents(_MD_CONVERT_PATH_CACHE . DIRECTORY_SEPARATOR . 'caches-files-sessioning.json'), true);
			else
				$sessions = array();
			if (!isset($sessions[basename($file)]))
				$sessions[basename($file)] = array('file' => $file, 'till' =>microtime(true) + mt_rand(3600*24*7.35,3600*24*14*8.75));
			foreach($sessions as $file => $values)
				if ($values['till']<time() && isset($values['till']))
				{
					if (file_exists($values['file']))
						unlink($values['file'])	;
					unset($sessions[$file]);
				}
			SaveToFile(_MD_CONVERT_PATH_CACHE . DIRECTORY_SEPARATOR . 'caches-files-sessioning.json', json_encode($sessions));
		}
	}
}

if (!function_exists("getArchivedZIPContentsArray")) {
	/**
	 * gets the contents of a zip archive in file listing
	 *
	 * @param string $zip_file
	 *
	 * @return array
	 */
	function getArchivedZIPContentsArray($zip_file = '')
	{
		$zip = zip_open($zip_file);
		$files = array();
		if ($zip) {
			while ($zip_entry = zip_read($zip)) {
				if (zip_entry_open($zip, $zip_entry, "r")) {
					$data = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
					$type = '';
					$parts = explode(".", basename(zip_entry_name($zip_entry)));
					$type = $parts[count($parts)-1];
					$files[md5($data)] = array('filename' => basename(zip_entry_name($zip_entry)), 'path' => dirname(zip_entry_name($zip_entry)), 'bytes' => strlen($data), 'type' => $type);
					zip_entry_close($zip_entry);
				}
			}
			zip_close($zip);
		}
		return $files;
	}
}

if (!function_exists("writeFontResourceHeader")) {
	/**
	 * Writes the resources for Font Base EOT files
	 *
	 * @param string $font
	 * @param string $licence
	 * @param array $values
	 *
	 * @return boolean
	 */
	function writeFontResourceHeader($font, $values = array())
	{
		xoops_loadLanguage('modinfo', _MD_CONVERT_MODULE_DIRNAME);
		$eotheader = explode("\n", file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'EOT-HEADER'));
		$nozero = false;
		$eotdata = explode("\n", file_get_contents($font));
		$eotheaderb = array();
		$found = false;
		foreach($eotheader as $line => $value)
		{
			if ($found == false)
				$eotheaderb[] = $value;
			else {
				$datafound = false;
				foreach($eotdata as $line => $valueb)
				{
					if (strpos($valueb, 'FontInfo 10 dict dup begin')==1)
					{
						$datafound = false;
						$start = false;
						$found = false;
						continue;
						continue;
					}
					if ($line==0 && $nozero==false)
					{
						$parts = explode(':', trim($eotheaderb[0]));
						if (strpos($valueb, $parts[0])>0)
						{
							$eotheaderb[0] = substr($valueb, 0, strpos($valueb, $parts[0]) - 1) . $eotheaderb[0];
							$nozero = true;
						}
					}
					if ($datafound==true) {
						$parts = explode(' ', trim($value));
						$partsb = explode(' ', trim($valueb));
						
						if ($start == true && $partsb[0]!=$parts[0] && !strpos(implode("\n", $eotheaderb), $partsb[0]) && !strpos(implode("\n", $eotheader), $partsb[0]))
							$eotheaderb[] = $valueb;
						elseif($start == true && !strpos(implode("\n", $eotheaderb), $parts[0])) {
							$eotheaderb[] = $value;
						}
						
					}
					if (trim($valueb) == '10 dict begin')
					{
						$datafound = true;
						$start = true;
					}
					if (strpos($valueb, 'FontInfo 10 dict dup begin')==1)
					{
						$datafound = false;
						$start = false;
						$found = false;
						continue;
						continue;
					}
				}
			}
			if (trim($value) == '10 dict begin')
			{
				$found = true;
			}
		}
		$found = false;
		$datafound = false;
		$start = false;
		unset($eotheaderb[count($eotheaderb)-1]);
		unset($eotheaderb[count($eotheaderb)-1]);
		$eotparts = array();
		foreach($eotheader as $line => $valueb)
		{
			$parts = explode(' ', trim($value));
			$eotparts[$parts[0]] = $parts;
		}
		foreach($eotdata as $line => $value)
		{
			if (strpos($value,'readonly def')>0)
			{
				$found=false;
			}
			if (substr($value,0,2)==' /')
			{
				$ptt = substr($value,2,strpos($value, " ", 4));
				$found = false;
				foreach($eotheader as $line => $valueb)
				{
					if (strpos($valueb, $ptt))
						$found = true;
				}
				if ($found != true)
					$eotheaderb[] = $value;
			}
			$parts = explode(' ', trim($value));
			if ($found == true)
			{
				if (!in_array($parts[0], array_keys($eotheaderb)) && !in_array($parts[0], array_keys($eotparts))) {
					$eotheaderb[$parts[0]] = $value;
				}
				$datafound = false;
				$start = true;
				
				foreach($eotheader as $line => $valueb)
				{
					if ($datafound==true) {
						
						$partsb = explode(' ', trim($valueb));
	
						
						if (!in_array($partsb[0], array_keys($eotheaderb))) {
							$eotheaderb[$partsb[0]] = $valueb;
						}
	
					}
					if (strpos($valueb, 'FontInfo')==1)
					{
						$datafound = $start = true;
						$partsb = explode(' ', trim($valueb));
						if (!in_array($partsb[0], array_keys($eotheaderb))) {
							$eotheaderb[$partsb[0]] = $valueb;
						}
					}
					if (trim($value) == 'end readonly def')
					{
						$datafound = false;
						$start = false;
						continue;
					}
				}
			}
			if (strpos($value, 'FontInfo')==1)
			{
				$found = true; $datafound = $start = true;
				if (!in_array($parts[0], array_keys($eotheaderb))) {
					$eotheaderb[$parts[0]] = $value;
				}
			}
		}
		$eotheaderb[] = 'end readonly def';
		
		$data = implode("\n", $eotheaderb);
		
		$data = str_replace('%fontapicompany%', _MD_CONVERT_DEFAULT_COMPANY, $data);
		$data = str_replace('%year%', date('Y'), $data);
		$data = str_replace('%fontcompany%', $values['company'], $data);
		$data = str_replace('%fontuploaddate%', date("Ymd", $values['uploaded']), $data);
		$data = str_replace('%apiurl%', XOOPS_URL, $data);
		$data = str_replace('%licensecode%', $values['license'], $data);
		$data = str_replace('%fontcopyright%', sprintf(_MD_CONVERT_LICENSE_NOTICE, $GLOBALS["xoopsConfig"]['sitename'], XOOPS_URL, $values['bizo'], $values['name'], $values['email'], _MD_CONVERT_LICENSE_NAME, _MD_CONVERT_LICENSE_URL), $data);
		
		
		foreach($values as $key => $value)
		{
			switch($key)
			{
				case 'title':
					$data = str_replace('%fontnamespaced%', spacerName($value), $data);
					$data = str_replace('%fontname%', sef(str_replace(" ", "", $value)), $data);
					break;
				case 'version':
					$data = str_replace('666.666', $value, $data);
					break;
				case 'date':
					$data = str_replace('%fontdate%', $value, $data);
					break;
				case 'creator':
					$data = str_replace('%fontcreator%', $value, $data);
					break;
				case 'type':
					$data = str_replace('%fonttype%', $value, $data);
					break;
				case 'matrix':
					$data = str_replace('%fontmatrix%', $value, $data);
					break;
				case 'bbox':
					$data = str_replace('%fontbbox%', $value, $data);
					break;
				case 'painttype':
					$data = str_replace('%fontpainttype%', $value, $data);
					break;
				case 'info':
					$data = str_replace('%fontinfo%', $value, $data);
					break;
				case 'family':
					$data = str_replace('%fontfamilyname%', $value, $data);
					break;
				case 'weight':
					$data = str_replace('%fontweight%', $value, $data);
					break;
				case 'fstype':
					$data = str_replace('%fontfstype%', $value, $data);
					break;
				case 'italicangle':
					$data = str_replace('%fontitalicangle%', $value, $data);
					break;
				case 'fixedpitch':
					$data = str_replace('%fontfixedpitch%', $value, $data);
					break;
				case 'underlineposition':
					$data = str_replace('%fontunderline%', $value, $data);
					break;
				case 'underlinethickness':
					$data = str_replace('%fontunderthickness%', $value, $data);
					break;
			}
		}		
		$eotheader = explode("\n", $data);
		
		$found = false;
		foreach($eotdata as $line => $value)
		{
			if ($found == false)
				unset($eotdata[$line]);
			if (trim($value) == 'end readonly def')
			{
				$found = true;
				continue;
			}
		}
		chmod($font, 777);
		unlink($font);
		putRawFile($font, $data."\n".implode("\n", $eotdata));
	}
}

if (!function_exists("getFontPreviewText")) {
	/**
	 * gets random preview text for font preview
	 *
	 * @return string
	 */
	function getFontPreviewText()
	{
		static $text = '';
		if (empty($text))
		{
			$texts = cleanWhitespaces(file(__DIR__ . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'preview-texts.diz'));
			shuffle($texts); shuffle($texts); shuffle($texts); shuffle($texts);
			if (count($_SESSION['previewtxt'])>0 && count($_SESSION['previewtxt']) < count($texts))
			{
				foreach($texts as $key => $txt)
					if (in_array($txt, $_SESSION['previewtxt']))
						unset($texts[$key]);
			} elseif(count($_SESSION['previewtxt'])==0 && count($_SESSION['previewtxt']) == count($texts)) {
				$_SESSION['previewtxt'] = array();
			}
			$attempts = 0;
			while(empty($text) && !in_array($text, $_SESSION['previewtxt']) || $attempts < 10)
			{
				$attempts++;
				$text = $texts[mt_rand(0, count($texts)-1)];
			}
			$_SESSION['previewtxt'][] = $text;
		}
		return $text;
	}
}


if (!function_exists("setFontPreviewText")) {
	/**
	 * gets random preview text for font preview
	 *
	 * @return string
	 */
	function setFontPreviewText($string = '')
	{
		static $text = '';
		if (empty($text))
		{
			$found = false;
			$texts = cleanWhitespaces(file($file = __DIR__ . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'preview-texts.diz'));
			foreach($texts as $value)
				if (strtolower($value) == strtolower($string))
					$found = true;
			if ($found == false)
			{
				$texts[] = $string;
				writeRawFile($file, implode("\n", $texts));
				return true;
			}
		}
		return false;
	}
}


if (!function_exists("getBaseFontValueStore")) {
	/**
	 * gets base font EOT Value Store
	 * 
	 * @param string $font
	 *
	 * @return array
	 */
	function getBaseFontValueStore($font, $defaulttitle = '')
	{
		xoops_loadLanguage('modinfo', _MD_CONVERT_MODULE_DIRNAME);
		$result = array('title' => $defaulttitle, 'uploaded' => microtime(true), 'license' => _MD_CONVERT_LICENSE_CODE . ' + ACADEMIC', 'licensename' => _MD_CONVERT_LICENSE_NAME . ' + ACADEMIC', 'company' => _MD_CONVERT_DEFAULT_COMPANY);
		if (file_exists($font))
		foreach(cleanWhitespaces(explode("\n",file_get_contents($font))) as $line)
		{
			if (substr($line,0, $from = strlen('%Version: ')) == '%Version: ')
			{
				$version = trim(substr($line, $from-1, strlen($line) - $from + 1));
				$parts = explode(array('~','~','!','@','#','$','%','^','&','*','(',')','-','_','+','=','{','[','}',']','|','\\',':',';','"','\'','<',',','>',"?","/"), $version);
				$version = false;
				foreach($parts as $value)
					if (is_numeric($value) && !is_string($value) && $version = false)
						$version = floatval($value);
				if ($version == false||is_string($version))
					$version = _MD_CONVERT_VERSION_INCREMENT;
				$result['version'] = $version;
			} elseif (substr($line,0, $from = strlen('%%CreationDate: ')) == '%%CreationDate: ')
			{
				$result['date'] = trim(substr($line, $from-1, strlen($line) - $from + 1));
			} elseif (substr($line,0, $from = strlen('%%Creator: ')) == '%%Creator: ')
			{
				$result['creator'] = trim(substr($line, $from-1, strlen($line) - $from + 1));
			} elseif (substr($line,0, $from = strlen('/FontType ')) == '/FontType ')
			{
				$result['type'] = trim(substr($line, $from-1, strlen($line) - $from - strlen(' def') + 1));
			} elseif (substr($line,0, $from = strlen('/FontMatrix [')) == '/FontMatrix [')
			{
				$result['matrix'] = trim(substr($line, $from, strlen($line) - $from - strlen(' ]readonly def') ));
			} elseif (substr($line,0, $from = strlen('/FontName /')) == '/FontName /')
			{
				$result['named'] = trim(substr($line, $from, strlen($line) - $from - strlen(' def')));
			} elseif (substr($line,0, $from = strlen('/FontBBox {')) == '/FontBBox { ')
			{
				$result['bbox'] = trim(substr($line, $from-1, strlen($line) - $from - strlen(' }readonly def') + 1));
			} elseif (substr($line,0, $from = strlen('/PaintType ')) == '/PaintType ')
			{
				$result['painttype'] = trim(substr($line, $from-1, strlen($line) - $from - strlen(' def') + 1));
			} elseif (substr($line,0, $from = strlen('/FontInfo ')) == '/FontInfo ')
			{
				$result['info'] = trim(substr($line, $from-1, strlen($line) - $from - strlen(' begin') + 1));
			} elseif (substr($line,0, $from = strlen('/FullName (')) == '/FullName (')
			{
				$result['title'] = trim(substr($line, $from, strlen($line) - $from - strlen(') readonly def') ));
				if (empty($result['title']))
					$result['title'] = $defaulttitle;
			} elseif (substr($line,0, $from = strlen('/FamilyName (')) == '/FamilyName (')
			{
				$result['family'] = trim(substr($line, $from, strlen($line) - $from - strlen(') readonly def') ));
			} elseif (substr($line,0, $from = strlen('/Weight (')) == '/Weight (')
			{
				$result['weight'] = trim(substr($line, $from, strlen($line) - $from - strlen(') readonly def') ));
			} elseif (substr($line,0, $from = strlen('/FSType ')) == '/FSType ')
			{
				$result['fstype'] = trim(substr($line, $from-1, strlen($line) - $from - strlen(' def') + 1));
			} elseif (substr($line,0, $from = strlen('/ItalicAngle ')) == '/ItalicAngle ')
			{
				$result['italicangle'] = trim(substr($line, $from-1, strlen($line) - $from - strlen(' def') + 1));
			} elseif (substr($line,0, $from = strlen(' /isFixedPitch  ')) == '/isFixedPitch ')
			{
				$result['fixedpitch'] = trim(substr($line, $from-1, strlen($line) - $from - strlen(' def') + 1));
			} elseif (substr($line,0, $from = strlen('/UnderlinePosition ')) == '/UnderlinePosition ')
			{
				$result['underlineposition'] = trim(substr($line-1, $from-1, strlen($line) - $from - strlen(' def') + 1));
			} elseif (substr($line,0, $from = strlen('/UnderlineThickness ')) == '/UnderlineThickness ')
			{
				$result['underlinethickness'] = trim(substr($line-1, $from-1, strlen($line) - $from - strlen(' def') + 1));
			} elseif (substr($line,0, $from = strlen('end readonly def')) == 'end readonly def')
			{
				return $result;
			}
		}
		return $result;
	}
}


if (!function_exists("deleteFilesNotListedByArray")) {
	/**
	 * deletes all files and folders contained within the path passed which do not match the array for file skipping
	 *
	 * @param string $dirname
	 * @param array $skipped
	 *
	 * @return array
	 */
	function deleteFilesNotListedByArray($dirname, $skipped = array())
	{
		$deleted = array();
		foreach(array_reverse(getCompleteFilesListAsArray($dirname)) as $file)
		{
			$found = false;
			foreach($skipped as $skip)
				if (strtolower(substr($file, strlen($file)-strlen($skip)))==strtolower($skip))
					$found = true;
			if ($found == false)
			{
				if (unlink($file))
				{
					$deleted[str_replace($dirname, "", dirname($file))][] = basename($file);
					rmdir(dirname($file));
				}
			}
		}
		return $deleted;
	}

}

if (!function_exists("getCompleteFilesListAsArray")) {
	/**
	 * Get a complete file listing for a folder and sub-folder
	 *
	 * @param string $dirname
	 * @param string $remove
	 *
	 * @return array
	 */
	function getCompleteFilesListAsArray($dirname, $remove = '')
	{
		foreach(getCompleteDirListAsArray($dirname) as $path)
			foreach(getFileListAsArray($path) as $file)
				$result[str_replace($remove, '', $path.DIRECTORY_SEPARATOR.$file)] = str_replace($remove, '', $path.DIRECTORY_SEPARATOR.$file);
		return $result;
	}

}


if (!function_exists("getCompleteDirListAsArray")) {
	/**
	 * Get a complete folder/directory listing for a folder and sub-folder
	 *
	 * @param string $dirname
	 * @param array $result
	 *
	 * @return array
	 */
	function getCompleteDirListAsArray($dirname, $result = array())
	{
		$result[$dirname] = $dirname;
		foreach(getDirListAsArray($dirname) as $path)
		{
			$result[$dirname . DIRECTORY_SEPARATOR . $path] = $dirname . DIRECTORY_SEPARATOR . $path;
			$result = getCompleteDirListAsArray($dirname . DIRECTORY_SEPARATOR . $path, $result);
		}
		return $result;
	}
	
}

if (!function_exists("getCompleteZipListAsArray")) {
	/**
	 * Get a complete zip archive for a folder and sub-folder
	 *
	 * @param string $dirname
	 * @param array $result
	 *
	 * @return array
	 */
	function getCompleteZipListAsArray($dirname, $result = array())
	{
		foreach(getCompleteDirListAsArray($dirname) as $path)
		{
			foreach(getZipListAsArray($path) as $file)
				$result[md5_file($path . DIRECTORY_SEPARATOR . $file)] =  $path . DIRECTORY_SEPARATOR . $file;
		}
		return $result;
	}
}


if (!function_exists("getCompleteFontsListAsArray")) {
	/**
	 * Get a complete all font files supported for a folder and sub-folder
	 *
	 * @param string $dirname
	 * @param array $result
	 *
	 * @return array
	 */
	function getCompleteFontsListAsArray($dirname, $result = array())
	{
		foreach(getCompleteDirListAsArray($dirname) as $path)
		{
			foreach(getFontsListAsArray($path) as $file=>$values)
				$result[$values['type']][md5_file($path . DIRECTORY_SEPARATOR . $values['file'])] = $path . DIRECTORY_SEPARATOR . $values['file'];
		}
		return $result;
	}
}

if (!function_exists("getDirListAsArray")) {
	/**
	 * Get a folder listing for a single path no recursive
	 *
	 * @param string $dirname
	 *
	 * @return array
	 */
    function getDirListAsArray($dirname)
    {
        $ignored = array(
            'cvs' ,
            '_darcs', '.git', '.svn');
        $list = array();
        if (substr($dirname, - 1) != '/') {
            $dirname .= '/';
        }
        if ($handle = opendir($dirname)) {
            while ($file = readdir($handle)) {
                if (substr($file, 0, 1) == '.' || in_array(strtolower($file), $ignored))
                    continue;
                if (is_dir($dirname . $file)) {
                    $list[$file] = $file;
                }
            }
            closedir($handle);
            asort($list);
            reset($list);
        }
		return $list;
    }
}

if (!function_exists("getFileListAsArray")) {
	/**
	 * Get a file listing for a single path no recursive
	 *
	 * @param string $dirname
	 * @param string $prefix
	 *
	 * @return array
	 */
    function getFileListAsArray($dirname, $prefix = '')
    {
        $filelist = array();
        if (substr($dirname, - 1) == '/') {
            $dirname = substr($dirname, 0, - 1);
        }
        if (is_dir($dirname) && $handle = opendir($dirname)) {
            while (false !== ($file = readdir($handle))) {
                if (! preg_match('/^[\.]{1,2}$/', $file) && is_file($dirname . '/' . $file)) {
                    $file = $prefix . $file;
                    $filelist[$file] = $file;
                }
            }
            closedir($handle);
            asort($filelist);
            reset($filelist);
        }
		return $filelist;
    }
}

if (!function_exists("getZipListAsArray")) {
	/**
	 * Get a zip file listing for a single path no recursive
	 *
	 * @param string $dirname
	 * @param string $prefix
	 *
	 * @return array
	 */
    function getZipListAsArray($dirname, $prefix = '')
    {
        $filelist = array();
        if ($handle = opendir($dirname)) {
           while (false !== ($file = readdir($handle))) {
               if (preg_match('/(\.zip)$/i', $file)) {
                   $file = $prefix . $file;
                   $filelist[$file] = $file;
               }
           }
           closedir($handle);
           asort($filelist);
           reset($filelist);
       }
       return $filelist;
    }
}

if (!function_exists("getFontsListAsArray")) {
	/**
	 * Get a font files listing for a single path no recursive
	 *
	 * @param string $dirname
	 * @param string $prefix
	 *
	 * @return array
	 */
	function getFontsListAsArray($dirname, $prefix = '')
	{
		$filelist = array();
		$formats = getFontFormats();
		if ($handle = opendir($dirname)) {
			while (false !== ($file = readdir($handle))) {
				foreach($formats as $format)
					if (substr(strtolower($file), strlen($file)-strlen(".".$format)) == strtolower(".".$format)) {
						$file = $prefix . $file;
						$filelist[$file] = array('file'=>$file, 'type'=>$format);
					}
			}
			closedir($handle);
		}
		return $filelist;
	}
}

if (!function_exists("getStampingShellExec")) {
	/**
	 * Get a bash shell execution command for stamping archives
	 *
	 * @return array
	 */
	function getStampingShellExec()
	{
		$ret = array();
		foreach(cleanWhitespaces(file(__DIR__ . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'packs-stamping.diz')) as $values)
		{
			$parts = explode("||", $values);
			$ret[$parts[0]] = $parts[1];
		}
		return $ret;
	}
}

if (!function_exists("getArchivingShellExec")) {
	/**
	 * Get a bash shell execution command for creating archives
	 *
	 * @return array
	 */
	function getArchivingShellExec()
	{
		$ret = array();
		foreach(cleanWhitespaces(file(__DIR__ . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'packs-archiving.diz')) as $values)
		{
			$parts = explode("||", $values);
			$ret[$parts[0]] = $parts[1];
		}
		return $ret;
	}
}

?>
