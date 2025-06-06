<?php

/**
* Automsg Secure Plugin  - Joomla 4.x/5.x plugin
* copyright 		: Copyright (C) 2025 ConseilGouz. All rights reserved.
* license    		: https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
*/

namespace ConseilGouz\Plugin\Automsg\Secure\Extension;

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Language\Text;
use Joomla\Event\SubscriberInterface;
use ConseilGouz\CGSecure\Helper\Cgipcheck;

class Secure extends CMSPlugin implements SubscriberInterface
{
    public $myname = 'AutomsgSecure';
    public $mymessage = '(AutoMsg) : try to access ...';
    public $errtype = 'w';	 // warning
    public $cgsecure_params;

    /**
     * @var boolean
     * @since 4.1.0
     */
    protected $autoloadLanguage = true;

    /**
     * @inheritDoc
     *
     * @return string[]
     *
     * @since 4.1.0
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onAutoMsgStart' 	=> 'startAutoMsg',
        ];
    }
    public function __construct(&$subject, $config)
    {
        $helperFile = JPATH_SITE . '/libraries/cgsecure/Helper/Cgipcheck.php';
        if (!is_file($helperFile)) {
            return;
        }
        parent::__construct($subject, $config);
        $this->cgsecure_params = Cgipcheck::getParams();
        $prefixe = $_SERVER['SERVER_NAME'];
        $prefixe = substr(str_replace('www.', '', $prefixe), 0, 2);
        $this->mymessage = $prefixe.$this->errtype.'-'.$this->mymessage;
    }
    /*
    *	onAutoMsgStart : Check IP on prepare Forms
    *
    *   @context  string   must contain com_automsg.register
    *   @params   Registry contain com_automsg parameters
    *   @response String   empty if OK, text if error
    *
    *	@return boolean    always true
    */

    public function startAutoMsg($event)
    {
        $lang = $this->getApplication()->getLanguage();
        $lang->load('plg_automsg_secure', JPATH_ADMINISTRATOR);
        $helperFile = JPATH_SITE . '/libraries/cgsecure/Helper/Cgipcheck.php';
        if (!is_file($helperFile)) {
            $event->setArgument('response', Text::_('PLG_AUTOMSG_SECURE_NEED'));
            return;
        }

        $context	= $event['context'];
        $params 	= $event['params'];
        $user 		= $event['user'];
        $txt		= $event['txt'];
        if ($context != 'com_automsg.register') {
            $event->setArgument('response', "Erreur context");
        }

        if (Cgipcheck::check_spammer($this, $this->myname)) {
            $event->setArgument('response', Text::_('PLG_AUTOMSG_SECURE_SPAMMER'));
        }
    }
}
