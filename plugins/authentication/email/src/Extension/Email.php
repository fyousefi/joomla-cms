<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Authentication.email
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Authentication\Email\Extension;

use Joomla\CMS\Authentication\Authentication;
use Joomla\CMS\Event\User\AuthenticationEvent;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\User\UserFactoryAwareTrait;
use Joomla\CMS\User\UserHelper;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Event\SubscriberInterface;
use Joomla\CMS\Factory;
use Joomla\Event\DispatcherInterface;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Plugin\Authentication\Joomla\Extension\Joomla;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Email Authentication plugin
 *
 * @since  1.5
 */
final class Email extends CMSPlugin implements SubscriberInterface
{

    use DatabaseAwareTrait;
    use UserFactoryAwareTrait;

    private $jauth;

    public function __construct(DispatcherInterface $dispatcher, $config = [])
    {
        parent::__construct($dispatcher, $config);
        $jauth = PluginHelper::getPlugin('authentication', 'joomla');
        $this->jauth = new Joomla($dispatcher, (array)$jauth);
    }

    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return  array
     *
     * @since   5.0.0
     */
    public static function getSubscribedEvents(): array
    {
        return ['onUserAuthenticate' => 'onUserAuthenticate'];
    }

    /**
     * This method should handle any authentication and report back to the subject
     *
     * @param   AuthenticationEvent  $event    Authentication event
     *
     * @return  void
     *
     * @since   1.5
     */
    public function onUserAuthenticate(AuthenticationEvent &$event): void
    {
        $credentials = $event->getCredentials();
        $response    = $event->getAuthenticationResponse();
        $options = $event->getOptions();

        $response->type = 'Email';

        // check if username is actually an email address
        if (!filter_var($credentials['username'], FILTER_VALIDATE_EMAIL)) {
            // Not an email address, so we return early, let the other plugins report the error
            return;
        }

        $result = $this->_getUser($credentials['username']);

        if ($result) {
            // update event with the actual username
            $credentials['username'] = $result;
            $event->setArgument('credentials', $credentials);

            // Set up the Joomla authentication plugin
            foreach(['Database','Application','UserFactory'] as $className) {
                $this->jauth->{'set'.$className}($this->{'get'.$className}());
            }

            // Set up the event
            $params = ['credentials'=>$credentials, 'options'=>$options, 'subject'=>$response];
            $authEvent = new AuthenticationEvent('onUserAuthenticate', $params);

            // Call the Joomla authentication plugin
            $this->jauth->onUserAuthenticate($authEvent);

            if ($response->status === \Joomla\CMS\Authentication\Authentication::STATUS_SUCCESS) {
                $response->username = $result;
                // Stop the event propagation, save resources
                $event->stopPropagation();
            }
        } else {
            $response->status = Authentication::STATUS_FAILURE;
            $response->error_message = $this->getApplication()->getLanguage()->_('JGLOBAL_AUTH_NO_USER');
        }
    }

    private function _getUser($email)
    {
        $email = strtolower($email);
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName(['username']))
            ->from($db->quoteName('#__users'))
            ->where('LOWER('.$db->quoteName('email') . ') LIKE :email')
            ->bind(':email', $email);

        $db->setQuery($query);
        $result = $db->loadObject();

        return $result->username;
    }
}
