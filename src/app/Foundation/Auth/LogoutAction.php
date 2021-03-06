<?php

namespace App\Foundation\Auth;

use App\Auth\Authenticator;
use App\Auth\Access\LoginRequiredTrait;
use Zend\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use function App\Foundation\trans;

class LogoutAction
{
    use LoginRequiredTrait;

    /**
     *
     */
    protected $authenticator;

    /**
     *
     */
    public function __construct(Authenticator $authenticator)
    {
        $this->authenticator = $authenticator;
    }

    /**
     *
     */
    protected function handlePermissionPassed(
        Request $request,
        Response $response,
        callable $next
    ) {
        $request = $this->authenticator->logout($request);
        $flash = $request->getAttribute('_messages');
        if (is_callable([$flash, 'success'])) {
            $flash->success($this->getLogoutSuccessMessage());
        }
        return new RedirectResponse('/');
    }

    /**
     * Give the user an helpfull message here
     *
     * @return string
     */
    protected function getPermissionDeniedMessage()
    {
        return trans()->has('auth.logout.error')
        ? trans()->get('auth.logout.error')
        : 'You can\'t logout while still not logged in';
    }

    /**
     *
     */
    protected function getLogoutSuccessMessage()
    {
        return trans()->has('auth.logout.success')
        ? trans()->get('auth.logout.success')
        : 'Come back later.';
    }
}
