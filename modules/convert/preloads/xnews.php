<?php
/**
 * System Preloads
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright   The XOOPS Project http://sourceforge.net/projects/xoops/
 * @license     GNU GPL 2 (http://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
 * @author      Simon Roberts (AKA +61405130385)
 * @version     $Id: xortify.php 8066 2011-11-06 05:09:33Z beckmi $
 */

defined('XOOPS_ROOT_PATH') or die('Restricted access');

define('_PL_CONVERT_NEWS_DIRNAME','xnews');
define('_PL_CONVERT_NEWS_TOPICID',1);

class ConvertXnewsPreload extends XoopsPreloadItem
{

	
	function eventCoreIncludeCommonEnd($args)
	{
		if (isset($GLOBALS['xoopsModule']))
		if (is_object($GLOBALS['xoopsModule']))
		if ($GLOBALS['xoopsModule']->getVar('dirname')!=_PL_CONVERT_NEWS_DIRNAME)
		{
			require_once dirname(dirname(dirname(__DIR__))) . '/include/' . 'functions.php';
			require_once dirname(__DIR__) . '/' . 'header.php';

			define("NW_SUBPREFIX", "nw");
			define("NW_MODULE_DIR_NAME", _PL_CONVERT_NEWS_DIRNAME);
			define("NW_MODULE_PATH", XOOPS_ROOT_PATH . "/modules/" . NW_MODULE_DIR_NAME);
			define("NW_MODULE_URL", XOOPS_URL . "/modules/" . NW_MODULE_DIR_NAME);
			define("NW_UPLOADS_NEWS_PATH", XOOPS_ROOT_PATH . "/uploads/" . NW_MODULE_DIR_NAME);
			define("NW_TOPICS_FILES_PATH", XOOPS_ROOT_PATH . "/uploads/" . NW_MODULE_DIR_NAME . "/topics");
			define("NW_ATTACHED_FILES_PATH", XOOPS_ROOT_PATH . "/uploads/" . NW_MODULE_DIR_NAME . "/attached");
			define("NW_TOPICS_FILES_URL", XOOPS_URL . "/uploads/" . NW_MODULE_DIR_NAME . "/topics");
			define("NW_ATTACHED_FILES_URL", XOOPS_URL . "/uploads/" . NW_MODULE_DIR_NAME . "/attached");
			
			//SEO activity
			include_once NW_MODULE_PATH . '/include/functions.php';
			require_once NW_MODULE_PATH . '/class/class.newsstory.php';
			require_once NW_MODULE_PATH . '/class/class.sfiles.php';
			require_once NW_MODULE_PATH . '/class/class.newstopic.php';
			require_once NW_MODULE_PATH . '/include/functions.php';
			
			if (file_exists(NW_MODULE_PATH . '/language/'.$xoopsConfig['language'].'/admin.php')) {
				require_once NW_MODULE_PATH . '/language/'.$xoopsConfig['language'].'/admin.php';
			} else {
				require_once NW_MODULE_PATH . '/language/english/admin.php';
			}
			
			$criteria = new CriteriaCompo(new Criteria('converted', 'Yes', 'LIKE'));
			$criteria->add(new Criteria('reported', 'No', 'LIKE'), 'AND');
			$criteria->add(new Criteria('fontid', '0', '<>'), 'AND');
			$criteria->setSort('`uploading`');
			$criteria->setOrder('ASC');
			$criteria->setLimit(1);
			
			$uploadsHandler = xoops_getModuleHandler('uploads', _MD_CONVERT_MODULE_DIRNAME);
			$fontsHandler = xoops_getModuleHandler('fonts', _MD_CONVERT_MODULE_DIRNAME);
			
			if ($uploadsHandler->getCount($criteria)>0)
			{
				foreach($uploadsHandler->getObjects($criteria) as $upload)
				{
					$font = $fontsHandler->get($upload->getVar('fontid'));
					if (is_object($font))
					{
						$text = file_get_contents(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'xnews-article.xcode');
			
						$text = str_replace('%fontname%', $font->getVar('name'), $text);
						$text = str_replace('%license%', $font->getVar('license'), $text);
						$text = str_replace('%licensecode%', $font->getVar('licensecode').'+ACADEMIC', $text);
						$text = str_replace('%uploadname%', $upload->getVar('name'), $text);
						$text = str_replace('%uploadorg%', $upload->getVar('company'), $text);
						$text = str_replace('%uploadwhen%', date('D, Y-m-d H:i:s', $upload->getVar('uploading')), $text);
						$text = str_replace('%downloadurl%', $font->getDownloadURL(), $text);
						$text = str_replace('%downloadfile%', $font->getVar('pack'), $text);
						$text = str_replace('%downloadsize%', number_format($font->getVar('zip-bytes'),0), $text);
						$text = str_replace('%numberfiles%', number_format($font->getVar('zip-files'),0), $text);
						$text = str_replace('%previewurl%', $font->getPreviewURL(), $text);
						$text = str_replace("\n", "\n", $text);
						
						$story = new nw_NewsStory();
						$story->setUid($upload->getVar('uid'));
						$story->setTitle("Font Uploaded: " . $font->getVar('name'));
						$story->setHometext($text);
						$story->setTopicId(intval(_PL_CONVERT_NEWS_TOPICID));
						$story->setHostname(xoops_getenv('REMOTE_ADDR'));
						$story->setNohtml(false);
						$story->setNosmiley(false);
						$story->setDobr(true);
						$story->setNotifyPub(false);
						$story->setType('admin');
						$story->setPublished( time() );
						$story->setExpired( 0 );
						$story->setBodytext('  ');
						$story->setApproved(true);
						$story->Settags($font->getVar('tags'));
						$result = $story->store();
						if ($result) {
							$upload->getVar('reporting','Yes');
							$uploadsHandler->insert($upload, true);
							$tag_handler = xoops_getmodulehandler('tag', 'tag');
							$tag_handler->updateByItem($font->getVar('tags'), $story->storyid(), _PL_CONVERT_NEWS_DIRNAME, 0);
							nw_updateCache();
							$upload->setVar('reported', 'Yes');
							$upload->setVar('reporting', microtime(true));
							$uploadsHandler->insert($upload, true);
							
							xoops_load("XoopsMailer");
							
							$mailer = new XoopsMailer();
							$mailer->setHTML(true);
							$mailer->setTemplateDir(dirname(__DIR__) . "/language/" . $GLOBALS['xoopsConfig']['language'] . "/mail_templates/");
							$mailer->setTemplate('upload_email_converted.html');
							$mailer->setFromEmail($GLOBALS['xoopsConfig']['adminmail']);
							$mailer->setFromName($GLOBALS['xoopsConfig']['sitename']);
							$mailer->setSubject("Font Converted: " . $font->getVar('name') . ' by ' . $upload->getVar('name'));
							$mailer->multimailer->addAddress($upload->getVar('email'), $upload->getVar('name'));
							$mailer->multimailer->addAttachment(dirname(__DIR__) . '/include/data/LICENSE', 'LICENSE.txt');
							$mailer->multimailer->addAttachment(dirname(__DIR__) . '/include/data/ACADEMIC', 'ACADEMIC.txt');
							$mailer->assign('X_FONTNAME', $font->getVar('name'));
							$mailer->assign('X_LICENSE', $font->getVar('license'));
							$mailer->assign('X_LICENSECODE', $font->getVar('licensecode').' + ACADEMIC');
							$mailer->assign('X_UPLOADNAME', $upload->getVar('name'));
							$mailer->assign('X_UPLOADORG', $upload->getVar('company'));
							$mailer->assign('X_UPLOADWHEN', date('D, Y-m-d H:i:s', $upload->getVar('uploading')));
							$mailer->assign('X_DOWNLOADURL', $font->getDownloadURL());
							$mailer->assign('X_DOWNLOADFILE', $font->getVar('pack'));
							$mailer->assign('X_DOWNLOADSSIZE', number_format($font->getVar('zip-bytes'),0));
							$mailer->assign('X_NUMBEROFFILES', number_format($font->getVar('zip-files'),0));
							$mailer->assign('X_PREVIEWURL', $font->getPreviewURL());
							$mailer->assign('X_NAMINGURL', $font->getNamingCueURL());
							$mailer->assign('X_EMAIL', $upload->getVar('email'));
							@$mailer->send(false);					
						}
					}
				}
			}
		}
	}
}
?>
