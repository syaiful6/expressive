<?php

namespace App\Auth;

use App\DateTime\DateTime;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerAwareTrait;
use Zend\EventManager\EventManagerAwareInterface;
use Psr\Http\Message\ServerRequestInterface as Request;

class AuthSignals implements EventManagerAwareInterface
{
    use EventManagerAwareTrait;

    protected $events;

    /**
     * Lazy load Zend\EventManager\EventManagerInterface
     *
     * @return Zend\EventManager\EventManagerInterface
     */
    public function getEventManager()
    {
        if (! $this->events) {
            $this->setEventManager(new EventManager());
        }

        return $this->events;
    }

    /**
     *
     */
    public function authenticateFailed(array $params)
    {
        $this->getEventManager()->trigger(__FUNCTION__, $this, $params);
    }

    /**
     *
     */
    public function userLoggedIn($user, Request $request = null)
    {
        $params = compact('user', 'request');
        $this->getEventManager()->trigger(__FUNCTION__, $this, $params);
    }

    /**
     *
     */
    public function userLoggedOut($user, Request $request = null)
    {
        $params = compact('user', 'request');
        $this->getEventManager()->trigger(__FUNCTION__, $this, $params);
    }

    /**
     *
     */
    protected function attachDefaultListeners()
    {
        $manager = $this->getEventManager();
        $manager->attach('userLoggedIn', function ($event) {
            $user = $event->getParam('user', false);
            if ($user) {
                $user->last_login = DateTime::now();
                $user->save();
            }
        });
    }
}
