<?php

namespace App\Action;

use App\Auth\Authenticator;
use App\Cache\RateLimiter;
use Zend\Diactoros\Stream;
use App\Foundation\Http\DispatchMethod;
use App\Foundation\Http\ValidateRequest;
use Zend\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Zend\Expressive\Template\TemplateRendererInterface as Template;

class LoginAction
{
    use DispatchMethod, ValidateRequest;

    /**
     * @var App\Auth\Authenticator
     */
    protected $authenticator;

    /**
     * @var Zend\Expressive\Template\TemplateRendererInterface
     */
    protected $template;

    /**
     * @var Illuminate\Contracts\Validation\Factory
     */
    protected $validationFactory;

    /**
     * @var App\Cache\RateLimiter
     */
    protected $limiter;

    /**
     *
     */
    public function __construct(
        Authenticator $authenticator,
        ValidationFactory $validationFactory,
        Template $template,
        RateLimiter $limiter
    ) {
        $this->authenticator = $authenticator;
        $this->validationFactory = $validationFactory;
        $this->template = $template;
        $this->limiter = $limiter;
    }

    /**
     * Display the login form
     */
    public function get(Request $request, Response $response, callable $next)
    {
        $html = $this->template->render('app::auth/login');
        $stream = new Stream('php://memory', 'wb+');
        $stream->write($html);
        return $response
            ->withBody($stream)
            ->withHeader('Content-Type', 'text/html');
    }

    /**
     *
     */
    public function post(Request $request, Response $response, callable $next)
    {
        $valid = $this->validateLogin($request);

        if ($valid) {
            return $this->formValid($request, $response, $next);
        }

        return $this->formInvalid($request, $response, $next);
    }

    /**
     *
     */
    protected function formValid(Request $request, Response $response, callable $next)
    {
        if ($this->hasTooManyLoginAttempts($request)) {
            return $this->sendResponseLockout($request);
        }

        $posted = $request->getParsedBody();
        $credential = [
            'email' => $posted['email'],
            'password' => $posted['password']
        ];
        $user = $this->authenticator->authenticate($credential);
        if ($user) {
            $this->authenticator->login($request, $user);

            return new RedirectResponse('/');
        }

        $this->incrementLoginAttempts($request);

        return $this->get($request, $response, $next);
    }

    /**
     * render with errors
     */
    protected function formInvalid(Request $request, Response $response, callable $next)
    {
        return $this->get($request, $response, $next);
    }

    /**
     * Determine if the user has too many failed login attempts.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function hasTooManyLoginAttempts(Request $request)
    {
        return $this->limiter->tooManyAttempts(
            $this->getThrottleKey($request),
            $this->maxLoginAttempts(),
            $this->lockoutTime()
        );
    }

    /**
     * Increment the login attempts for the user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return int
     */
    protected function incrementLoginAttempts(Request $request)
    {
        $this->limiter->hit(
            $this->getThrottleKey($request)
        );
    }

    /**
     *
     */
    protected function getThrottleKey($request)
    {
        $post = $request->getParsedBody();
        $ip = $this->extractClientIpFromRequest($request);
        return $post['email'].'|'.$ip;
    }

    /**
     *
     */
    protected function maxLoginAttempts()
    {
        return 5;
    }

    /**
     *
     */
    protected function lockoutTime()
    {
        return 60;
    }

    /**
     * I believe this should done on framework
     */
    protected function extractClientIpFromRequest($request)
    {
        if ($request->hasHeader('REMOTE_ADDR', false)) {
            $remote = $request->getHeader('REMOTE_ADDR');
            return $remote[0];
        }
        $proxies = $request->getHeader('X_FORWARDED_FOR');
        $proxies = array_map('trim', explode(',', $proxies[0]));
        if (empty($proxies)) {
            return '';
        }
        $ip = array_pop($proxies);
        return $ip;
    }

    /**
     *
     */
    protected function validateLogin(Request $request)
    {
        return $this->isValid($request, [
            'email' => 'required',
            'password' => 'required'
        ]);
    }
}
