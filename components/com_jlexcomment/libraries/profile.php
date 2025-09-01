<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

class CommentProfile
{
    protected $type = 'none';

    protected $loaded = false;

    protected $profiles = array ();

    public function __construct ($type)
    {
    	$this->type = $type;
    }

    private function load($files)
    {
    	if($this->loaded==true)
            return true;

    	$this->loaded = true;

    	foreach($files as $file)
    	{
    		if(file_exists($file))
                require_once $file;
    	}
    }

    public function getUser($userid, $email='')
    {
    	$key = !empty($email) ? $email : $userid;

    	if(array_key_exists($key, $this->profiles))
    	{
    		return $this->profiles[$key];
    	}
        
        $config = JLexCommentHelper::getConfig();
        $default = JUri::root().'media/jcm/avatar/jcm_avatar.png';
        $defaultConfig = JUri::root().$config->get('default_avatar', 'media/jcm/avatar/jcm_avatar.png');

        if(preg_match('/^\s*$/', $defaultConfig))
            $defaultConfig = $default;

        $thumb 	= null;
        $link 	= null;

        if($userid>0)
        {
            $data = $this->_3rd($userid, $email);
            if(!$data)
            {
                if($userid>0)
                {
                    $db = JFactory::getDbo();
                    $query = "SELECT auth_picture FROM #__jlexcomment_users WHERE userid=" . $userid;
                    $result = $db->setQuery($query)->loadResult();

                    if($result)
                    {
                        $thumb = preg_match("/^https?:\/\//", $result) ? $result : (JUri::base (true) . '/media/jcm/avatar/' . $result);
                    }
                }
            } else {
                $link   = $data['link'];
                $thumb  = $data['thumb'];
            }
        }

        // fix
        if(!$userid && $email!='' && $this->type=='gravatar')
        {
            $data=$this->_3rd(0, $email);
            if($data){
                $link   = $data['link'];
                $thumb  = $data['thumb'];
            }
        }

        if(empty($thumb))
        {
            $thumb = $defaultConfig;
            if($userid>0 && $config->get('author_thumb',1) && $config->get('rand_avatar',0)==1)
            {
                require_once dirname(__FILE__).'/avatar.php';
                $avatar_file = 'r'.time().rand(0, 99).rand(0, 99).'.png';
                $avatar = new randomAvatarsGenerator();
                $avatar->generate();
                $avatar->draw();
                $avatar->saveImage(JPATH_ROOT.'/media/jcm/avatar', $avatar_file);

                $db = JFactory::getDbo();
                $query = $db->getQuery(true);

                $query->select('COUNT(*)')
                      ->from('#__jlexcomment_users')
                      ->where('userid='.$db->quote($userid));

                $exist = $db->setQuery($query)->loadResult();

                if($exist)
                {
                    $query->clear()
                          ->update('#__jlexcomment_users')
                          ->set('auth_picture='.$db->quote($avatar_file))
                          ->where('userid='.$db->quote($userid));
                } else {
                    $query->clear()
                          ->insert('#__jlexcomment_users')
                          ->columns($db->quoteName(['userid', 'created', 'auth', 'auth_picture', 'auth_id', 'auth_url']))
                          ->values(implode(',', [$userid, $db->quote(JFactory::getDate()->toSql()), $db->quote('joomla'), $db->quote($avatar_file), $db->quote(''), $db->quote('')]));
                }

                $db->setQuery($query)->execute();
            }
        }

        $response = array(
            'link' => $link,
            'thumb' => $thumb
        );

        $this->profiles[$key] = $response;
        
        return $response;
    }

    private function _3rd ( $userid, $email )
    {
        $db = JFactory::getDbo();

        switch ( $this->type ) {
            case 'djclassfields':   
                require_once(JPATH_ROOT.'/components/com_djclassifieds/model.php');
                $djmodel = new DJClassifiedsModel();
                $p = $djmodel->getProfile($userid);

                if($p)
                {
                    if(array_key_exists('img', $p))
                    {
                        $thumb = JUri::root(true).$p['img']->path.$p['img']->name.'.'.$p['img']->ext;
                    }
                    
                    $link = $p["uri"];
                }

                break;
                
            case 'easysocial':
                $this->load (array(
                    JPATH_ADMINISTRATOR . '/components/com_easysocial/includes/easysocial.php'
                ));

                if(!class_exists('ES'))
                {
                    return false;
                }

                $thumb = ES::user($userid)->getAvatar();
                $link = ES::user($userid)->getPermalink();
                break;

            case 'easyprofile':
                $this->load (array(
                    JPATH_SITE.'/components/com_jsn/helpers/helper.php'
                ));

                if (! class_exists('JsnHelper'))
                {
                    return false;
                }

                $user = JsnHelper::getUser($userid);

                $thumb = JUri::root() . $user->getValue("avatar");
                $link = $user->getLink();
                break;

            case 'k2':
                $this->load (array(
                    JPATH_ROOT . '/components/com_k2/helpers/route.php',
                    JPATH_ROOT . '/components/com_k2/helpers/utilities.php'
                ));

                $result = $db->setQuery("SELECT image FROM #__k2_users WHERE userID={$userid}")->loadResult();
                $thumb = is_null($result) ? null : JUri::base(true) . '/media/k2/users/' . $result;
                $link = K2HelperRoute::getUserRoute($userid);
                break;

            case 'cb':
                $this->load([
                    JPATH_ADMINISTRATOR . '/components/com_comprofiler/plugin.foundation.php'
                ]);

                if(!class_exists('CBuser'))
                    return false;

                $cbUser = CBuser::getInstance($userid, false);
                if(!$cbUser) return false;

                $thumb = $cbUser->avatarFilePath();
                $link = cbSef('index.php?option=com_comprofiler&amp;task=userProfile&amp;user=' . $userid);
                break;

            case 'easyblog':
                $this->load (array(
                    JPATH_ADMINISTRATOR . '/components/com_easyblog/includes/easyblog.php'
                ));

                if(!class_exists('EB'))
                {
                    return false;
                }

                $thumb = EB::user($userid)->getAvatar();
                $link = EB::user($userid)->getPermalink();

                break;

            case 'easydiscuss':
                $this->load (array(
                    JPATH_ADMINISTRATOR . '/components/com_easydiscuss/includes/easydiscuss.php'
                ));

                if(!class_exists('ED'))
                {
                    return false;
                }

                $thumb = ED::user($userid)->getAvatar();
                $link = ED::user($userid)->getPermalink();

                break;

            case 'gravatar':
                $default = JUri::base() . 'components/com_jlexreview/assets/images/default.jpg';
                $emailKey = md5(strtolower(trim($email)));
                
                $thumb = 'http://www.gravatar.com/avatar/' . $emailKey . '?s=100&amp;d=' . urlencode($default) . '&amp;s=64';
                $link = 'http://www.gravatar.com/' . md5($email);
                break;

            case 'jomsocial':
                $this->load (array(
                    JPATH_ROOT . '/components/com_community/libraries/core.php'
                ));

                if (! class_exists('CFactory'))
                {
                    return false;
                }

                $thumb = CFactory::getUser($userid)->getThumbAvatar();
                $link = CRoute::_('index.php?option=com_community&view=profile&userid=' . $userid);
                break;

            case 'kunena':
                $this->load (array(
                    JPATH_ADMINISTRATOR . '/components/com_kunena/libraries/factory.php'
                ));

                if (! class_exists('KunenaFactory'))
                {
                    return false;
                }

                $thumb = KunenaFactory::getUser($userid)->getAvatarURL('kavatar');
                $link = KunenaFactory::getProfile($userid)->getProfileURL($userid, '');
                break;

            case 'kunena3':
                $this->load (array(
                    JPATH_PLATFORM . '/kunena/factory.php'
                ));

                if (! class_exists('KunenaFactory'))
                {
                    return false;
                }

                $thumb = KunenaFactory::getUser($userid)->getAvatarURL(72, 72);
                $link = KunenaFactory::getUser($userid)->getURL();
                break;

            case 'jlexreview':
                $path = JPATH_ROOT . "/components/com_jlexreview/jlexreview.php";
                if(!file_exists($path))
                    return false;

                require_once JPATH_ROOT . "/components/com_jlexreview/libs/helper.php";

                $thumb = JUri::base(true).'/components/com_jlexreview/assets/images/default.jpg';

                $query = $db->getQuery(true);
                $query->select('u.username, ju.*')
                      ->from('#__users u')
                      ->leftJoin('#__jlexreview_users ju ON u.id=ju.userid')
                      ->where('u.id='.$db->quote($userid));

                $user_tmp  = $db->setQuery($query,0,1)->loadObject();

                if(!empty($user_tmp->auth_picture))
                {
                    $thumb = preg_match('/^(http(s)?:\/\/)/', $user_tmp->auth_picture) ? $user_tmp->auth_picture : JUri::base(true).'/media/jlexreview/avatar/' . $user_tmp->auth_picture;
                }

                $slug  = JFilterOutput::stringURLSafe($userid.":".$user_tmp->username);

                $link = JLexReviewHelperSite::route('index.php?option=com_jlexreview&view=profile&id='.$slug);

                break;

            case "jlexguestbook":
                $this->load([
                        JPATH_ROOT."/components/com_jlexguestbook/libraries/helper.php",
                        JPATH_ROOT."/components/com_jlexguestbook/libraries/profile.php"
                    ]);
                
                $bs = new JLexGBProfile("jlexguestbook");
                $dt = $bs->getUser($userid, $email);

                $link  = $dt->link;
                $thumb = $dt->thumb;
                break;

            case "field":
                $thumb = JUri::base(true).'/index.php?option=com_jlexcomment&task=user.getAvatar&id='.$userid;
                $link = null;
                break;

            default :
                return false;
        }

        return array (
                'link'  => $link,
                'thumb' => $thumb
            );
    }
}