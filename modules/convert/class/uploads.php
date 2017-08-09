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

if (!defined('_MD_CONVERT_MODULE_DIRNAME')) {
	return false;
}

//*
require_once (__DIR__ . DIRECTORY_SEPARATOR . 'objects.php');

/**
 * Class for Uploads in Fonts2Web.org.uk Font Converter
 *
 * For Table:-
 * <code>
 * CREATE TABLE `convert_uploads` (
 *   `id` mediumint(12) NOT NULL AUTO_INCREMENT,
 *   `path` varchar(255) NOT NULL DEFAULT '',
 *   `file` varchar(128) NOT NULL DEFAULT '',
 *   `file-bytes` int(14) NOT NULL DEFAULT '0',
 *   `extension` varchar(10) NOT NULL DEFAULT '',
 *   `converted` enum('Yes','No') NOT NULL DEFAULT 'No',
 *   `reported` enum('Yes','No') NOT NULL DEFAULT 'No',
 *   `tweeted` enum('Yes','No','Unsupported') NOT NULL DEFAULT 'Unsupported',
 *   `fontid` mediumint(12) NOT NULL DEFAULT '0',
 *   `uploading` float(24,8) NOT NULL DEFAULT '0.00000000',
 *   `downloading` float(24,8) NOT NULL DEFAULT '0.00000000',
 *   `reporting` float(24,8) NOT NULL DEFAULT '0.00000000',
 *   `tweeting` float(24,8) NOT NULL DEFAULT '0.00000000',
 *   `uid` int(12) NOT NULL DEFAULT '0',
 *   `name` varchar(255) NOT NULL DEFAULT '',
 *   `company` varchar(255) NOT NULL DEFAULT '',
 *   `email` varchar(255) NOT NULL DEFAULT '',
 *   `twitter` varchar(42) NOT NULL DEFAULT '',
 *   PRIMARY KEY (`id`),
 *   KEY `SEARCH` (`file`,`extension`,`converted`,`fontid`,`reported`,`tweeted`,`uploading`,`downloading`,`reporting`,`tweeting`)
 * ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
 * </code>
 * @author Simon Roberts (wishcraft@users.sourceforge.net)
 * @copyright copyright (c) 2015 labs.coop
 */
class convertUploads extends convertXoopsObject
{

	var $handler = '';
	
    function __construct($id = null)
    {   	
    	
        $this->initVar('id', XOBJ_DTYPE_INT, null, false);
        $this->initVar('path', XOBJ_DTYPE_TXTBOX, null, false, 255);
        $this->initVar('file', XOBJ_DTYPE_TXTBOX, null, false, 128);
        $this->initVar('file-bytes', XOBJ_DTYPE_INT, null, false);
        $this->initVar('extension', XOBJ_DTYPE_TXTBOX, null, false, 10);
        $this->initVar('converted', XOBJ_DTYPE_ENUM, 'No', false, false, false, getEnumeratorValues(basename(__FILE__), 'converted'));
        $this->initVar('reported', XOBJ_DTYPE_ENUM, 'No', false, false, false, getEnumeratorValues(basename(__FILE__), 'reported'));
        $this->initVar('tweeted', XOBJ_DTYPE_ENUM, 'Unsupported', false, false, false, getEnumeratorValues(basename(__FILE__), 'tweeted'));
        $this->initVar('fontid', XOBJ_DTYPE_INT, null, false);
        $this->initVar('uploading', XOBJ_DTYPE_FLOAT, null, false);
        $this->initVar('downloading', XOBJ_DTYPE_FLOAT, null, false);
        $this->initVar('reporting', XOBJ_DTYPE_FLOAT, null, false);
        $this->initVar('tweeting', XOBJ_DTYPE_FLOAT, null, false);
        $this->initVar('uid', XOBJ_DTYPE_INT, null, false);
        $this->initVar('name', XOBJ_DTYPE_TXTBOX, null, false, 255);
        $this->initVar('company', XOBJ_DTYPE_TXTBOX, null, false, 255);
        $this->initVar('email', XOBJ_DTYPE_TXTBOX, null, false, 255);
        $this->initVar('twitter', XOBJ_DTYPE_TXTBOX, null, false, 42);
        
        
        $this->handler = __CLASS__ . 'Handler';
        if (!empty($id) && !is_null($id))
        {
        	$handler = new $this->handler;
        	$this->assignVars($handler->get($id)->getValues(array_keys($this->vars)));
        }
        
    }

}


/**
 * Handler Class for Uploads in Fonts2Web.org.uk Font Converter
 * @author Simon Roberts (wishcraft@users.sourceforge.net)
 * @copyright copyright (c) 2015 labs.coop
 */
class convertUploadsHandler extends convertXoopsObjectHandler
{
	

	/**
	 * Table Name without prefix used
	 * 
	 * @var string
	 */
	var $tbl = 'convert_uploads';
	
	/**
	 * Child Object Handling Class
	 *
	 * @var string
	 */
	var $child = 'convertUploads';
	
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
	var $envalued = 'file';
	
    function __construct(&$db) 
    {
    	if (!is_object($db))
    		$db = $GLOBAL["xoopsDB"];
        parent::__construct($db, $this->tbl, $this->child, $this->identity, $this->envalued);
    }
}
?>