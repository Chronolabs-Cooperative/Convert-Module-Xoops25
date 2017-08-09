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
 * @version			1.0.1
 * @link        	http://fonts2web.org.uk
 * @link        	http://fonts.labs.coop
 * @link			http://internetfounder.wordpress.com
 */
if (!defined(_MD_CONVERT_MODULE_DIRNAME))
	define('_MD_CONVERT_MODULE_DIRNAME', basename(dirname(__DIR__)));

if (!defined('_MD_CONVERT_MODULE_DIRNAME')) {
	return false;
}
//*
require_once (dirname(__DIR__) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'functions.php');
require_once (__DIR__ . DIRECTORY_SEPARATOR . 'objects.php');
require_once (__DIR__ . DIRECTORY_SEPARATOR . 'xcp' . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR . 'xcp.class.php');

/**
 * Class for Fonts in Fonts2Web.org.uk Font Converter
 *
 * For Table:-
 * <code>
 * CREATE TABLE `convert_fonts` (
 *   `id` mediumint(12) NOT NULL AUTO_INCREMENT,
 *   `uploadid` mediumint(12) NOT NULL DEFAULT '0',
 *   `identity` varchar(32) NOT NULL DEFAULT '',
 *   `year` int(4) NOT NULL DEFAULT '0',
 *   `month` int(2) NOT NULL DEFAULT '0',
 *   `daynum` int(2) NOT NULL DEFAULT '0',
 *   `day` enum('Sun','Mon','Tue','Wed','Thu','Fri','Sat') NOT NULL DEFAULT 'Sat',
 *   `week` int(2) NOT NULL DEFAULT '0',
 *   `hour` int(2) NOT NULL DEFAULT '0',
 *   `uid` int(13) NOT NULL DEFAULT '0',
 *   `glyphs` int(16) NOT NULL DEFAULT '0',
 *   `comments` int(16) NOT NULL DEFAULT '0',
 *   `storage` enum('XOOPS_DATA','XOOPS_UPLOADS') NOT NULL DEFAULT 'XOOPS_DATA',
 *   `path` varchar(255) NOT NULL DEFAULT '',
 *   `pack` varchar(128) NOT NULL DEFAULT '',
 *   `fontfile` varchar(255) NOT NULL DEFAULT '',
 *   `syndicated` enum('Yes','No') NOT NULL DEFAULT 'No',
 *   `sydnication` varchar(255) NOT NULL DEFAULT '',
 *   `sydnicating` float(24,8) NOT NULL DEFAULT '0.00000000',
 *   `cachefile` varchar(255) NOT NULL DEFAULT '',
 *   `cached` float(24,8) NOT NULL DEFAULT '0.00000000',
 *   `accessed` float(24,8) NOT NULL DEFAULT '0.00000000',
 *   `deleted` float(24,8) NOT NULL DEFAULT '0.00000000',
 *   `zip-bytes` int(14) NOT NULL DEFAULT '0',
 *   `zip-files` int(14) NOT NULL DEFAULT '0',
 *   `open-bytes` int(14) NOT NULL DEFAULT '0',
 *   `downloads` int(16) NOT NULL DEFAULT '0',
 *   `downloaded` float(24,8) NOT NULL DEFAULT '0.00000000',
 *   `kb-downloaded` float(44,4) NOT NULL DEFAULT '0.0000',
 *   `previews` int(16) NOT NULL DEFAULT '0',
 *   `previewed` float(24,8) NOT NULL DEFAULT '0.00000000',
 *   `kb-previewed` float(44,4) NOT NULL DEFAULT '0.0000',
 *   `kb-glyphed` float(44,4) NOT NULL DEFAULT '0.0000',
 *   `name` varchar(255) NOT NULL DEFAULT '',
 *   `version` float(8,4) NOT NULL DEFAULT '0.0000',
 *   `license` varchar(255) NOT NULL DEFAULT '',
 *   `licensecode` varchar(8) NOT NULL DEFAULT '',
 *   `tags` varchar(255) NOT NULL DEFAULT '',
 *   `referee` varchar(16) NOT NULL DEFAULT '',
 *   `barcode` varchar(8) NOT NULL DEFAULT '',
 *   PRIMARY KEY (`id`),
 *   KEY `SEARCH` (`uploadid`,`storage`,`fontfile`,`referee`,`barcode`,`name`,`identity`,`syndicated`,`glyphs`,`uid`,`comments`,`accessed`,`deleted`,`cached`)
 * ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
 * </code>
 * @author Simon Roberts (wishcraft@users.sourceforge.net)
 * @copyright copyright (c) 2015 labs.coop
 */
class convertFonts extends convertXoopsObject
{

	var $handler = '';
	
    function __construct($id = null)
    {   	
    	
        self::initVar('id', XOBJ_DTYPE_INT, null, false);
        self::initVar('uploadid', XOBJ_DTYPE_INT, null, false);
        self::initVar('identity', XOBJ_DTYPE_TXTBOX, null, false, 32);
        self::initVar('year', XOBJ_DTYPE_INT, null, false);
        self::initVar('month', XOBJ_DTYPE_INT, null, false);
        self::initVar('daynum', XOBJ_DTYPE_INT, null, false);
        self::initVar('day', XOBJ_DTYPE_ENUM, 'Mon', false, false, false, getEnumeratorValues(basename(__FILE__), 'day'));
        self::initVar('hour', XOBJ_DTYPE_INT, null, false);
        self::initVar('week', XOBJ_DTYPE_INT, null, false);
        self::initVar('uid', XOBJ_DTYPE_INT, null, false);
        self::initVar('glyphs', XOBJ_DTYPE_INT, null, false);
        self::initVar('comments', XOBJ_DTYPE_INT, null, false);
        self::initVar('storage', XOBJ_DTYPE_ENUM, 'XOOPS_DATA', false, false, false, getEnumeratorValues(basename(__FILE__), 'storage'));
        self::initVar('path', XOBJ_DTYPE_TXTBOX, null, false, 255);
        self::initVar('pack', XOBJ_DTYPE_TXTBOX, null, false, 128);
        self::initVar('fontfile', XOBJ_DTYPE_TXTBOX, null, false, 255);
        self::initVar('syndicated', XOBJ_DTYPE_ENUM, 'No', false, false, false, getEnumeratorValues(basename(__FILE__), 'syndicated'));
        self::initVar('sydnication', XOBJ_DTYPE_INT, null, false);
        self::initVar('sydnicating', XOBJ_DTYPE_FLOAT, null, false);
        self::initVar('cachefile', XOBJ_DTYPE_TXTBOX, null, false, 255);
        self::initVar('cached', XOBJ_DTYPE_FLOAT, null, false);
        self::initVar('accessed', XOBJ_DTYPE_FLOAT, null, false);
        self::initVar('deleted', XOBJ_DTYPE_FLOAT, null, false);
        self::initVar('zip-bytes', XOBJ_DTYPE_INT, null, false);
        self::initVar('zip-files', XOBJ_DTYPE_INT, null, false);
        self::initVar('open-bytes', XOBJ_DTYPE_INT, null, false);
        self::initVar('downloads', XOBJ_DTYPE_INT, null, false);
        self::initVar('downloaded', XOBJ_DTYPE_FLOAT, null, false);
        self::initVar('kb-downloaded', XOBJ_DTYPE_FLOAT, null, false);
        self::initVar('previews', XOBJ_DTYPE_INT, null, false);
        self::initVar('previewed', XOBJ_DTYPE_FLOAT, null, false);
        self::initVar('kb-previewed', XOBJ_DTYPE_FLOAT, null, false);
        self::initVar('kb-glyphed', XOBJ_DTYPE_FLOAT, null, false);
        self::initVar('name', XOBJ_DTYPE_TXTBOX, null, false, 255);
        self::initVar('version', XOBJ_DTYPE_FLOAT, null, false);
        self::initVar('license', XOBJ_DTYPE_TXTBOX, null, false, 255);
        self::initVar('licensecode', XOBJ_DTYPE_TXTBOX, null, false, 8);
        self::initVar('tags', XOBJ_DTYPE_TXTBOX, null, false, 255);
        self::initVar('referee', XOBJ_DTYPE_TXTBOX, null, false, 16);
        self::initVar('barcode', XOBJ_DTYPE_TXTBOX, null, false, 8);
        
        $this->handler = __CLASS__ . 'Handler';
        if (!empty($id) && !is_null($id))
        {
        	$handler = new $this->handler;
        	self::assignVars($handler->get($id)->getValues(array_keys($this->vars)));
        }
        
    }
	
    /**
     * 
     * @return mixed
     */
    function getFileDIZ() {
    	
    	xoops_loadLanguage('modinfo', _MD_CONVERT_MODULE_DIRNAME);
    	
    	$template = file_get_contents(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'file.diz');
    	$template = str_replace("%filename",  sef($this->getvar('name')).'.zip', $template);
    	$template = str_replace("%released",  date("D, d/M/y H:i:s"), $template);
    	$template = str_replace("%version",  $this->getVar('version'), $template);
    	$template = str_replace("%name",  $this->getvar('name'), $template);
    	$template = str_replace("%barcode",  $this->getvar('barcode'), $template);
    	$template = str_replace("%referee",  $this->getvar('referee'), $template);
    	$template = str_replace("%files",  $this->getvar('zip-files')+1, $template);
    	$template = str_replace("%bytes",  $this->getvar('open-bytes'), $template);
    	if ($GLOBALS['convertConfigsList']['htaccess'])
    		$url = XOOPS_URL . '/' . $GLOBALS['convertConfigsList']['base'] . '/download/'.$this->getvar('barcode').".zip";
    	else
    		$url = XOOPS_URL . '/modules/" . basename(dirname(__DIR__)) . "/download.php?id='.$this->getvar('barcode');
    			 
    	$template = str_replace("%downloadurl", $url, $template);
    	$template = str_replace("%license", _MD_CONVERT_LICENSE_NAME . " + Academic", $template);
    	$template = str_replace("%licenseurl", _MD_CONVERT_LICENSE_URL, $template);
    			
    	$uploadsHandler = xoops_getModuleHandler('uploads', _MD_CONVERT_MODULE_DIRNAME);
    	$upload = $uploadsHandler->get($this->getVar('uploadid'));
    	$template = str_replace("%converter",  $upload->getVar('name'), $template);
    	$template = str_replace("%company",  $upload->getVar('company'), $template);
    
    	// files list
    	$filesHandler = xoops_getModuleHandler('files', _MD_CONVERT_MODULE_DIRNAME);
    	$criteria = new Criteria('fontid', $this->getVar('id'));
    	$criteria->setSort('ASC');
    	$criteria->setOrder('`path`');
    	
    	$files = $filesHandler->getVar($criteria);
    	$fflz = array();
    	$maxchars = 0;
    	foreach($files as $id => $file)
    	{
    		$fflz[$id] =  " * " . $file->getVar('path') . DIRECTORY_SEPARATOR . $file->getVar('file');
    		if ($maxchars < strlen($fflz[$id]))
    			$maxchars = strlen($fflz[$id]);
  		}
  		foreach($files as $id => $file)
  		{
  			$fflz[$id] = str_repeat(' ', $maxchars - strlen($fflz[$id])) . '  -  ' .number_format($file->getVar('bytes')) . ' bytes';
  		}
  		$template = str_replace("%filelist",  implode("\n", $fflz), $template);
  		return $template;
    }
    
    /**
     * 
     * @param string $type
     * @return string
     */
    function getFontDisplayURL($type='referee')
    {
    	require (dirname(__DIR__) . DIRECTORY_SEPARATOR . 'header.php');
    	if ($GLOBALS['convertConfigsList']['htaccess']) {
    		$url = XOOPS_URL . '/' . $GLOBALS['convertConfigsList']['base'] . '/font/'.sef($this->getVar('name')).'/'.$this->getVar($type).'.html';
    	} else {
    		$url = XOOPS_URL . '/modules/' . _MD_CONVERT_MODULE_DIRNAME . '/font.php?id='.$this->getVar($type);
    	}
    	return $url;
    }

    /**
     *
     * @param string $type
     * @return string
     */
    function getCallbackURL($type='referee')
    {
    	require (dirname(__DIR__) . DIRECTORY_SEPARATOR . 'header.php');
    	if ($GLOBALS['convertConfigsList']['htaccess']) {
    		$url = XOOPS_URL . '/' . $GLOBALS['convertConfigsList']['base'] . '/callback/'.$this->getVar($type).'.api';
    	} else {
    		$url = XOOPS_URL . '/modules/' . _MD_CONVERT_MODULE_DIRNAME . '/callback.php?id='.$this->getVar($type);
    	}
    	return $url;
    }
    
	/**
	 * 
	 * @param string $type
	 * @return string
	 */
    function getNamingCueURL($type='referee')
    {
    	require (dirname(__DIR__) . DIRECTORY_SEPARATOR . 'header.php');
    	if ($GLOBALS['convertConfigsList']['htaccess']) {
    		$url = XOOPS_URL . '/' . $GLOBALS['convertConfigsList']['base'] . '/naming/'.$this->getVar($type).'.png';
    	} else {
    		$url = XOOPS_URL . '/modules/' . _MD_CONVERT_MODULE_DIRNAME . '/naming.php?id='.$this->getVar($type);
    	}
    	return $url;
    }

    /**
     *
     * @param string $type
     * @return string
     */
    function getPreviewURL($type='referee')
    {
    	require (dirname(__DIR__) . DIRECTORY_SEPARATOR . 'header.php');
    	if ($GLOBALS['convertConfigsList']['htaccess']) {
    		$url = XOOPS_URL . '/' . $GLOBALS['convertConfigsList']['base'] . '/preview/'.$this->getVar($type).'.png';
    	} else {
    		$url = XOOPS_URL . '/modules/' . _MD_CONVERT_MODULE_DIRNAME . '/preview.php?id='.$this->getVar($type);
    	}
    	return $url;
    }


    /**
     *
     * @param string $type
     * @return string
     */
    function getDownloadURL($type='referee')
    {
    	require (dirname(__DIR__) . DIRECTORY_SEPARATOR . 'header.php');
    	if ($GLOBALS['convertConfigsList']['htaccess']) {
    		$url = XOOPS_URL . '/' . $GLOBALS['convertConfigsList']['base'] . '/download/'.$this->getVar($type).'.zip';
    	} else {
    		$url = XOOPS_URL . '/modules/' . _MD_CONVERT_MODULE_DIRNAME . '/download.php?id='.$this->getVar($type);
    	}
    	return $url;
    }
    
    /**
     * 
     * @param string $format
     * @return string[]|unknown[]
     */
	function getFontFile($format = 'eot')
	{
		require (dirname(__DIR__) . DIRECTORY_SEPARATOR . 'header.php');
		mkdir($dir = constant("_MD_CONVERT_PATH_UPLOADS") . DIRECTORY_SEPARATOR . sha1(microtime(true)), 0777, true);
		copy(self::getCachedFile(), $file = $dir . DIRECTORY_SEPARATOR . self::getVar('fontfile'));
		$scripts = file(dirname(__DIR__) . DIRECTORY_SEPARATOR . "include" . DIRECTORY_SEPARATOR . "data" . DIRECTORY_SEPARATOR . "convert-fonts-distribution.pe");
		foreach($scripts as $key => $value)
			if ($key>0)
				if (!strpos($value, $format))
					unset($scripts[$key]);
		file_put_contents($script = $dir . DIRECTORY_SEPARATOR . $this->getVar('referee') . '.pe', implode('',$scripts));
		$outt = array();
		exec($exe = sprintf(DIRECTORY_SEPARATOR . "usr" . DIRECTORY_SEPARATOR . "bin" . DIRECTORY_SEPARATOR . "fontforge -script \"%s\" \"%s\"", $script, $file), $outt, $return);
		foreach(getCompleteFontsListAsArray($dir) as $typal => $files)
		{
			if ($typal == $format)
			{
				foreach($files as $fl)
				{
					$data = file_get_contents($fl);
					$mime = mime_content_type($fl);
					continue;
					continue;
				}
			}
		}
		$outt = array();
		exec("rm -Rf $dir", $outt, $return);
		if (!empty($data) && !empty($mime))
		{
			return array('data' => $data, 'mime' => $mime);
		}
	}
	
	/**
	 *
	 * @param string $type
	 * @return string
	 */
	function getFontURL($type='referee', $format = 'eot')
	{
		require (dirname(__DIR__) . DIRECTORY_SEPARATOR . 'header.php');
		if ($GLOBALS['convertConfigsList']['htaccess']) {
			$url = XOOPS_URL . '/' . $GLOBALS['convertConfigsList']['base'] . '/css/font/'.$this->getVar($type).'.'.$format;
		} else {
			$url = XOOPS_URL . '/modules/' . _MD_CONVERT_MODULE_DIRNAME . '/css-font.php?id='.$this->getVar($type).'&format='.$format;
		}
		return $url;
	}
	
	
    /**
     *
     * @param string $type
     * @return string
     */
    function getGlyphsURL($type='referee')
    {
    	require (dirname(__DIR__) . DIRECTORY_SEPARATOR . 'header.php');
    	if ($GLOBALS['convertConfigsList']['htaccess']) {
    		$url = XOOPS_URL . '/' . $GLOBALS['convertConfigsList']['base'] . '/glyph/'.$this->getVar($type).'-%s.png';
    	} else {
    		$url = XOOPS_URL . '/modules/' . _MD_CONVERT_MODULE_DIRNAME . '/glyph.php?id='.$this->getVar($type).'&char=%s';
    	}
    	return $url;
    }
 
    
    /**
     *
     * @param string $type
     * @return string
     */
    function getCSSURL($type='referee')
    {
    	require (dirname(__DIR__) . DIRECTORY_SEPARATOR . 'header.php');
    	if ($GLOBALS['convertConfigsList']['htaccess']) {
    		$url = XOOPS_URL . '/' . $GLOBALS['convertConfigsList']['base'] . '/css/'.$this->getVar($type).'.css';
    	} else {
    		$url = XOOPS_URL . '/modules/' . _MD_CONVERT_MODULE_DIRNAME . '/css.php?id='.$this->getVar($type);
    	}
    	return $url;
    }
    
    /**
     * 
     * @param string $type
     * @return string[]
     */
    function getCSSURLs($type='referee')
    {
    	require (dirname(__DIR__) . DIRECTORY_SEPARATOR . 'header.php');
    	static $fonts = array();
    	if (empty($fonts))
    	{
	    	foreach(array_keys(fontsUseragentSupportedArray()) as $fonttype)
	    	{
	    		$fonts[$fonttype] = $this->getFontURL($type, $fonttype);
	    	}
    	}
    	return $fonts;
    }
    
    /**
     * 
     * @param string $type
     * @return string
     */
    function getCSS($type = 'referee')
    {
    	require (dirname(__DIR__) . DIRECTORY_SEPARATOR . 'header.php');
    	static $names = array();
    	static $css = array();
    	if (empty($names))
    	{
    		$names[] = $this->getvar('name');
    		$names[] = str_replace(" ", "-", strtolower($this->getvar('name')));
    		$tags = explode(",", str_replace(" ", "", $this->getvar('tags')));
    		sort($tags);
    		if (count($tags)>1)
    			$names[] = ucwords(strtolower(implode(' ', $tags)));
    		$names[] = $this->getvar('barcode');
    		$names[] = $this->getvar('referee');
    	}
    
    	foreach($names as $name)
    	{
    		if (empty($css[$name]))
    		{
    			$buff = array("local('||')");
    			foreach($fonts = self::getCSSURLs($type) as $typal => $url)
    			{
    				$buff[] = "url('".$url."') format('".$typal."')";
    					
    			}
    			$css[$name]= array();
    			$css[$name][] = "";
    			$css[$name][] = "/** Font: ".$this->getvar('name')." **/";
    			$css[$name][] = "@font-face {";
    			$css[$name][] = "\tfont-family: '$name';";
    			$css[$name][] = "\tsrc: url('".$fonts['eot']."');";
    			$css[$name][] = "\tsrc: ".implode(", ", $buff) .";";
    			$css[$name][] = "}";
    		}
    	}
    	$sheet = "/**\n * @see " . self::getFontDisplayURL($type) . "\n * @see " . self::getDownloadURL($type) . "\n**/";
    	foreach($css as $name => $styles)
    		$sheet .= "\n\n" . implode("\n", $styles);
    	return $sheet;
    }
    
    /**
     * 
     * @return string
     */
   	function getCachedFile()
   	{
   		require (dirname(__DIR__) . DIRECTORY_SEPARATOR . 'header.php');
   		
   		if (!is_dir(_MD_CONVERT_PATH_CACHE))
   			mkdir(_MD_CONVERT_PATH_CACHE, 0777, true);
   		
   		if (strlen($this->getVar('cachefile'))==0 || !file_exists($file = _MD_CONVERT_PATH_CACHE . DIRECTORY_SEPARATOR . $this->getVar('cachefile')) || filesize($file) == 0)
   		{
   			if (strlen($this->getVar('cachefile'))==0)
   			{
   				$this->setVar('cachefile', 'convert_'.sha1(microtime(true).__FILE__.XOOPS_DB_PASS.$this->getVar('id')) . '.ttf');
   			}
   			if (file_exists($pack = _MD_CONVERT_PATH_REPOSITORY . $this->getVar('path') . DIRECTORY_SEPARATOR . $this->getVar('pack')))
   			{
   				$data = getArchivedZIPFile($pack, 'ttf');
   				if (strlen($data))
   				{
	   				writeRawFile($file = _MD_CONVERT_PATH_CACHE . DIRECTORY_SEPARATOR . $this->getVar('cachefile'), $data);
	   				$this->setVar('cached', microtime(true));
   				} else 
   					trigger_error(_ERR_CONVERT_FONTS_REPORESNOTFOUND);
   			}
   		}
   		$this->setVar('accessed', microtime(true));
   		$fontHandler = new convertFontsHandler();
   		$fontHandler->insert($this, true);
   		if (!file_exists($file))
   		{
   			trigger_error(_ERR_CONVERT_FONTS_CACHEFILEMISSING);
   			exit(0);
   		}
   		return $file;
   	}

   	/**
   	 * Gets the Preview Tile in image/png
   	 *
   	 * @return image/png
   	 */
   	function getGlyphPreview($char = 32)
   	{
   	
   		require_once __DIR__ . DIRECTORY_SEPARATOR .  'WideImage' . DIRECTORY_SEPARATOR . 'WideImage.php';
   		 
   		$img = WideImage::load(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'font-glyph.png');
   		if ($state == 'jpg')
   		{
   			$bg = $img->allocateColor(255, 255, 255);
   			$img->fill(0, 0, $bg);
   		}
   		$height = $img->getHeight();
   		$canvas = $img->getCanvas();
   		$canvas->useFont($this->getCachedFile(), $height-46, $img->allocateColor(0, 0, 0));
   		$canvas->writeText("center", "center + 7", "&#$char;");
   		$canvas->useFont($this->getCachedFile(), 10, $img->allocateColor(0, 0, 0));
   		$canvas->writeText('center', 'top + 3', '&amp;#'.$char.';');
   	
   		return $img->output('png');
   	}
   	
   	/**
   	 * Gets the Preview Tile in image/png
   	 * 
   	 * @return image/png
   	 */
   	function getPreview()
   	{

   		require_once __DIR__. DIRECTORY_SEPARATOR . 'barcode' . DIRECTORY_SEPARATOR . 'BarcodeGenerator.php';
   		require_once __DIR__. DIRECTORY_SEPARATOR . 'barcode' . DIRECTORY_SEPARATOR . 'BarcodeGeneratorJPG.php';
   		require_once __DIR__ . DIRECTORY_SEPARATOR .  'WideImage' . DIRECTORY_SEPARATOR . 'WideImage.php';
   		
   		
   		// Generates Barcode's
   		$generator = new Picqer\Barcode\BarcodeGeneratorJPG();
   		$bcdata = $generator->getBarcode($this->getVar('barcode'), 'C128');
   		writeRawFile($barcodejpg = constant("_MD_CONVERT_PATH_UPLOADS") . DIRECTORY_SEPARATOR . sha1(microtime(true).$this->getVar('barcode')), $bcdata);   		
   		
   		// Generates Preview
   		$barcode = WideImage::load($barcodejpg);
   		$img = WideImage::load(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'font-preview.png');
   		$height = $img->getHeight();
   		$lsize = 34;
   		$ssize = 11;
   		$step = mt_rand(8,11);
   		$canvas = $img->getCanvas();
   		$i=0;
   		while($i<$height)
   		{
   			$canvas->useFont($this->getCachedFile(), $point = $ssize + ($lsize - (($lsize  * ($i/$height)))), $img->allocateColor(0, 0, 0));
   			$canvas->writeText(19, $i, getFontPreviewText());
   			$i=$i+$point + $step;
   		}
   		$canvas->writeText('right - 13', 'bottom - ' . ($barcode->getHeight() + 19), "Font Name: ".$this->getVar('name'));
   		$img->merge($barcode, 'right - 13', 'bottom - 13', 100)->saveToFile($previewfile = XOOPS_ROOT_PATH . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . 'complexity' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'backgrounds' . DIRECTORY_SEPARATOR . sha1(microtime(true).$this->getVar('name').$this->getVar('id').'.png').'.png');
   		unset($img);
   		return file_get_contents($previewfile);
   	}
   	
    /**
     * Gets the name card for the font
     * 
     * @return image/png
     */
    function getNamingCue()
    {
    	require_once __DIR__ . DIRECTORY_SEPARATOR . 'WideImage' . DIRECTORY_SEPARATOR . 'WideImage.php';
    	if (strlen($this->getVar('name'))<=9)
    	{
    		$img = WideImage::load(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'font-title-small.png');
    	} elseif (strlen($this->getVar('name'))<=12)
    	{
    		$img = WideImage::load(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'font-title-medium.png');
    	}elseif (strlen($this->getVar('name'))<=21)
    	{
    		$img = WideImage::load(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'font-title-large.png');
    	} else
    	{
    		$img = WideImage::load(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'font-title-extra.png');
    	}
    	$height = $img->getHeight();
    	$point = $height * (32/99);
    	$canvas = $img->getCanvas();
    	$canvas->useFont($this->getCachedFile(), $point, $img->allocateColor(0, 0, 0));
    	$canvas->writeText('center', 'center', $this->getVar('name'));
    	header("Content-type: ".getMimetype('png'));
   		return $img->output('png');
   		exit(0);
    }
    
    /**
     * 
     * @return mixed|string|NULL|boolean
     */
    function getHistoryTile()
    {
    	$result = array();
    	$result['urls']['naming'] = $this->getNamingCueURL();
    	$result['urls']['download'] = $this->getDownloadURL();
    	$result['urls']['display'] = $this->getFontDisplayURL();
    	$result['font']['name'] = $this->getVar('name');
    	$result['font']['pack'] = $this->getVar('pack');
    	$result['font']['version'] = $this->getVar('version');
    	$result['font']['license']['title'] = $this->getVar('license');
    	$result['font']['license']['code'] = $this->getVar('licensecode') . " + ACADEMIC";
    	$result['font']['glyphs'] = $this->getVar('glyphs');
    	$result['font']['zip']['mbs'] = number_format($this->getVar('zip-bytes') / 1024 / 1024, 2);
    	$result['font']['zip']['files'] = $this->getVar('zip-files');
    	$uploadsHandler = xoops_getModuleHandler('uploads', _MD_CONVERT_MODULE_DIRNAME);
    	$upload = $uploadsHandler->get($this->getVar('uploadid'));
    	$result['font']['uploader']['name'] = $upload->getVar('name');
    	$result['font']['uploader']['company'] = $upload->getVar('company');
    	$result['io']['uploaded'] = date("D, Y-m-d H:i:s", $upload->getVar('uploading'));
    	$result['io']['downloaded'] = date("D, Y-m-d H:i:s", $this->getVar('downloaded'));
    	$result['io']['downloads'] = $this->getVar('downloads');
    	return $result;
    }
}

/**
 * Handler Class for Fonts in Fonts2Web.org.uk Font Converter
 * @author Simon Roberts (wishcraft@users.sourceforge.net)
 * @copyright copyright (c) 2015 labs.coop
 */
class convertFontsHandler extends convertXoopsObjectHandler
{
	

	/**
	 * Table Name without prefix used
	 * 
	 * @var string
	 */
	var $tbl = 'convert_fonts';
	
	/**
	 * Child Object Handling Class
	 *
	 * @var string
	 */
	var $child = 'convertFonts';
	
	/**
	 * Child Object Identity Key
	 *
	 * @var string
	 */
	var $identity = 'id';
	
	/**
	 * Child Object Default Envaluing Costs
	 *
	 * @var string
	 */
	var $envalued = 'referee';
	
    function __construct(&$db) 
    {
    	if (!is_object($db))
    		$db = $GLOBAL["xoopsDB"];
        parent::__construct($db, $this->tbl, $this->child, $this->identity, $this->envalued);
    }
    
    /**
     * 
     * @return number
     */
    function getTotalUploads()
    {
    	$criteria = new Criteria(1,1);
    	return $this->getCount($criteria);
    }
    
    /**
     *
     * @return number
     */
    function getTotalDownloads()
    {
    	$sql = "SELECT sum(`downloads`) as `downloads` FROM `" . $this->db->prefix($this->tbl) . "`";
    	list($result) = $this->db->fetchRow($this->db->queryF($sql));
    	return $result;
    }

    /**
     *
     * @return number
     */
    function getTotalDownloadedMbytes()
    {
    	$sql = "SELECT sum(`kb-downloaded`) / 1024 as `downloads` FROM `" . $this->db->prefix($this->tbl) . "`";
    	list($result) = $this->db->fetchRow($this->db->queryF($sql));
    	return number_format($result, 2);
    }

    /**
     *
     * @return number
     */
    function getTotalFilesInCache()
    {
    	return count(getCompleteFilesListAsArray(_MD_CONVERT_PATH_CACHE));
    }

    /**
     *
     * @return number
     */
    function getTotalMbsInCache()
    {
    	$bytes = 0;
    	foreach(getCompleteFilesListAsArray(_MD_CONVERT_PATH_CACHE) as $file => $values)
    		$bytes = $bytes + filesize($file);
    	return number_format($bytes / 1024 / 1024, 2);
    }
    

    /**
     *
     * @return number
     */
    function getTotalFilesInRepo()
    {
    	return count(getCompleteFilesListAsArray(_MD_CONVERT_PATH_REPOSITORY));
    }
    
    /**
     *
     * @return number
     */
    function getTotalMbsInRepo()
    {
    	$bytes = 0;
    	foreach(getCompleteFilesListAsArray(_MD_CONVERT_PATH_REPOSITORY) as $file => $values)
    		$bytes = $bytes + filesize($file);
    	return number_format($bytes / 1024 / 1024, 2);
    }
    
    /**
     * Retrieves Expired Cache Items
     * 
     * @param number $seconds
     * @return XoopsObject[]
     */
    function getExpiredCacheObjects($seconds = 3600)
    {
    	$ret = array();
    	$sql = "SELECT `id` FROM `" . $this->db->prefix($this->tbl) . "` WHERE `cached` > `deleted` AND `accessed` < '" . time() - $seconds . "' AND `deleted` < `accessed`";
    	$result = $this->db->queryF($sql);
    	while($row = $this->db->fetchArray($result))
    	{
    		$ret[$row[id]] = $this->get($row['id']);
    	}
    	return $ret;
    }
    
    /**
     * Retrieves a free hash information header
     * 
     * @param string $type
     * @param number $minlen
     * @param number $maxlen
     */
    function getUnusedHash($type = 'referee', $minlen = 9, $maxlen = 16)
    {
    	set_time_limit(360);
    	$hash = '';
    	while (strlen($hash) == 0)
    	{
    		$data = md5(microtime(true) . json_encode($_SERVER) . microtime(true) . __DIR__);
    		$len = mt_rand($minlen, $maxlen);
    		$xcp = new xcp($data, mt_rand(0,253), $len);
    		$referee = $xcp->crc;
    		$criteria = new Criteria($type, $referee, 'LIKE');
    		if ($this->getCount($criteria)==0)
    		{
    			return $hash = $referee;
    		}
    	}
    }
    
    /**
     * Get an Object by Barcode or Referee Hash
     * 
     * @param string $hash
     * @return unknown|boolean
     */
    function getByHash($hash = '')
    {
    	$criteria = new CriteriaCompo(new Criteria('barcode', $hash, 'LIKE'), 'OR');
    	$criteria->add(new Criteria('referee', $hash, 'LIKE'), 'OR');
    	$criteria->setLimit(1);
    	foreach($this->getObjects($criteria) as $object)
    		return $object;
    	return false;
    }
    
    /**
     * Inserts font records
     * 
     * {@inheritDoc}
     * @see XoopsPersistableObjectHandler::insert()
     */
    function insert($object, $force = true)
    {
    	if ($object->isNew())
    	{
    		if (strlen($object->getVar('referee'))<=4 || is_null($object->getVar('referee')))
    			$object->setVar('referee',$this->getUnusedHash('referee',8,15));
    		if (strlen($object->getVar('barcode'))<=4 || is_null($object->getVar('barcode')))
    			$object->setVar('barcode',$this->getUnusedHash('barcode',4,7));
    		$object->setVar('year',date('Y'));
    		$object->setVar('month',date('m'));
    		$object->setVar('daynum',date('d'));
    		$object->setVar('day',date('D'));
    		$object->setVar('week',date('W'));
    		$object->setVar('hour',date('H'));
    		
    		// Aligns Syndication with fonts.labs.coop
    		$criteria = new Criteria('name', $object->getVar('name'), 'LIKE');
    		foreach($this->getObjects($criteria) as $obj)
    		{
    			if (strlen($object->getVar('identity'))==0 && strlen($obj->getVar('identity'))>0)
    				$object->setVar('identity',$obj->getVar('identity'));
    			if ($object->getVar('syndicated') == 'No' && $obj->getVar('syndicated') == 'Yes')
    			{
    				$object->setVar('syndicated',$obj->getVar('syndicated'));
    				$object->setVar('syndication',$obj->getVar('syndication'));
    				$object->setVar('syndicating',$obj->getVar('syndicating'));
    			}
    		}
    	}
    	$itemid = parent::insert($object, $force);
    	
    	// Inserts Tags
    	$tag_handler = xoops_getmodulehandler('tag', 'tag');
    	$tag_handler->updateByItem($object->getVar('tag'), $itemid, basename(dirname(__DIR__)), $catid = 0);
    	return $itemid;
    }
    
    /**
     * 
     */
    function getRecentDivs()
    {
    	$criteria = new Criteria(1,1);
    	$criteria->setOrder('DESC');
    	$criteria->setSort('`year` DESC, `month` DESC, `daynum` DESC, `hour`');
    	$criteria->setLimit(14);
    	$objects = $this->getObjects($criteria);
    	$html = array();
    	$first = 0;
      	foreach($objects as $object)
      	{
      		if (strlen($object->getVar('name'))>0)
      		{
      			if (is_object($GLOBALS['xoTheme']))
      				$GLOBALS['xoTheme']->addStylesheet($object->getCSSURL());
	      		if ($first == 0)
	      		{
	      			$html[] = "\t\t<div style='width: 100%; padding: 3px; height: 65px; margin-bottom: 7px; clear: both;' class='".($odd!='odd'?$odd='odd':$odd='even')."'>";
	      		}
	      		$first++;
	      		$html[] = "\t\t\t<div style='width: 45.49%; clear: none; border: 2px dotted #823497; margin: 7px; postion: relative; display: block; padding: 7px; float: ".($float!='left'?$float='left':$odd='right').";'>";
	      		$html[] = "\t\t\t\t<a href='".$object->getFontDisplayURL()."' target='_blank'><span style=\"font-family: '" . $object->getVar('name') . "' !important; font-size: 1.99em\">".$object->getVar('name')."</span></a>";
	      		$html[] = "\t\t\t</div>";
	      		if ($first == 2)
	      		{
	      			$html[] = "\t\t</div>";
	      			$first = 0;
	      		}
      		}
      	}
      	return implode("\n", $html);
    }
}


?>