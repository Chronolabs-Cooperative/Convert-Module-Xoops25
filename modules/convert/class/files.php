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
 * Class for Files in Fonts2Web.org.uk Font Converter
 *
 * For Table:-
 * <code>
 * CREATE TABLE `convert_files` (
 *   `id` mediumint(96) NOT NULL AUTO_INCREMENT,
 *   `fontid` mediumint(12) NOT NULL DEFAULT '0',
 *   `uploadid` mediumint(12) NOT NULL DEFAULT '0',
 *   `path` varchar(128) NOT NULL DEFAULT '',
 *   `file` varchar(128) NOT NULL DEFAULT '',
 *   `extension` varchar(15) NOT NULL DEFAULT '',
 *   `bytes` int(12) NOT NULL DEFAULT '0',
 *   `md5` varchar(32) NOT NULL DEFAULT '',
 *   `sha1` varchar(44) NOT NULL DEFAULT '',
 *   PRIMARY KEY (`id`),
 *   KEY `SEARCH` (`fontid`,`uploadid`,`file`,`extension`,`md5`,`sha1`)
 * ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
 * </code>
 * @author Simon Roberts (wishcraft@users.sourceforge.net)
 * @copyright copyright (c) 2015 labs.coop
 */
class convertFiles extends convertXoopsObject
{

	var $handler = '';
	
    function __construct($id = null)
    {   	
    	
        self::initVar('id', XOBJ_DTYPE_INT, null, false);
        self::initVar('fontid', XOBJ_DTYPE_INT, null, false);
        self::initVar('uploadid', XOBJ_DTYPE_INT, null, false);
        self::initVar('path', XOBJ_DTYPE_TXTBOX, '/', false, 128);
        self::initVar('file', XOBJ_DTYPE_TXTBOX, null, false, 128);
        self::initVar('extension', XOBJ_DTYPE_TXTBOX, null, false, 15);
        self::initVar('bytes', XOBJ_DTYPE_INT, null, false);
        self::initVar('md5', XOBJ_DTYPE_TXTBOX, null, false, 32);
        self::initVar('sha1', XOBJ_DTYPE_TXTBOX, null, false, 44);
        
        $this->handler = __CLASS__ . 'Handler';
        if (!empty($id) && !is_null($id))
        {
        	$handler = new $this->handler;
        	self::assignVars($handler->get($id)->getValues(array_keys($this->vars)));
        }
        
    }

}


/**
 * Handler Class for Files in Fonts2Web.org.uk Font Converter
 * @author Simon Roberts (wishcraft@users.sourceforge.net)
 * @copyright copyright (c) 2015 labs.coop
 */
class convertFilesHandler extends convertXoopsObjectHandler
{
	

	/**
	 * Table Name without prefix used
	 * 
	 * @var string
	 */
	var $tbl = 'convert_files';
	
	/**
	 * Child Object Handling Class
	 *
	 * @var string
	 */
	var $child = 'convertFiles';
	
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
	var $envalued = 'md5';
	
    function __construct(&$db) 
    {
    	if (!is_object($db))
    		$db = $GLOBAL["xoopsDB"];
        parent::__construct($db, $this->tbl, $this->child, $this->identity, $this->envalued);
    }
    
    /**
     * Insert files listed in zip in database 
     * 
     * {@inheritDoc}
     * @see XoopsPersistableObjectHandler::insert()
     */
    function insert($object = null, $force = true)
    {
    	if ($object->isNew())
    	{
    		if (strpos($object->getVar('path'), '.ufo'))
    		{
    			$criteria = new CriteriaCompo(new Criteria('fontid', $object->getVar('fontid')));
    			$criteria->add(new Criteria('uploadid', $object->getVar('uploadid')));
    			$criteria->add(new Criteria('extension', 'ufo'));
    			if ($this->getCount($criteria)==0)
    			{
    				$obj = $this->create();
    				$obj->setVar('fontid', $object->getVar('fontid'));
    				$obj->setVar('uploadid', $object->getVar('uploadid'));
    				$obj->setVar('path', dirname($object->getVar('path')));
    				$obj->setVar('file', basename($object->getVar('path')));
    				$obj->setVar('bytes', $object->getVar('bytes'));
    				$obj->setVar('sha1', $object->getVar('sha1'));
    				$obj->setVar('md5', $object->getVar('md5'));
    				$obj->setVar('extension', 'ufo');
    				return parent::insert($obj, $force);
    			} else {
    				foreach($this->getObjects($criteria) as $obj)
    				{
    					$obj->setVar('bytes', $obj->getVar('bytes') + $object->getVar('bytes'));
    					$obj->setVar('sha1', sha1($obj->getVar('sha1') . $object->getVar('sha1')));
    					$obj->setVar('md5', md5($obj->getVar('md5') . $object->getVar('md5')));
    					return parent::insert($obj, $force);
    				}
    			}
    		}
    	}
    	return parent::insert($object, $force);
    }
}
?>