<?php

namespace App\Action\Auth;

use App\Auth\User;
use App\Auth\ModelBackend;
use App\DateTime\DateTime;
use Zend\Diactoros\Stream;
use Illuminate\Support\MessageBag;
use App\Auth\Access\UserPassesTestTrait;
use App\Foundation\Http\BaseActionMiddleware;
use Zend\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Zend\Expressive\Template\TemplateRendererInterface as Template;

class RegisterAction extends BaseActionMiddleware
{
    use UserPassesTestTrait {
        __invoke as userPassedTest;
    }

    /**
     *
     */
    protected $template;

    /**
     * @var App\Auth\Authenticator
     */
    protected $authenticator;

    /**
     *
     */
    public function __construct(Template $template, Authenticator $authenticator)
    {
        $this->template = $template;
        $this->authenticator = $authenticator;
    }

    /**
     *
     */
    protected function testCallback(Request $request)
    {
        return function () use ($request) {
            $user = $request->getAttribute('user');
            return $user && !$user->isAuthenticate();
        };
    }

    /**
     *
     */
    public function __invoke(Request $request, Response $response, callable $next)
    {
        return $this->userPassedTest($request, $response, $next);
    }

    /**
     *
     */
    protected function handlePermissionPassed(
        Request $request,
        Response $response,
        callable $next
    ) {
        return parent::__invoke($request, $response, $next);
    }

    /**
     *
     */
    public function get(Request $request, Response $response, callable $next)
    {
        $html = $this->template->render('app::auth/register', [
            'error' => new MessageBag()
        ]);
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
        $valid = $this->validateRegister($request);

        if ($valid) {
            return $this->formValid($request, $response, $next);
        }

        return $this->formInvalid($request, $response, $next);
    }

    /**
     * render with errors
     */
    protected function formInvalid(Request $request, Response $response, callable $next)
    {
        $html = $this->template->render('app::auth/register', [
            'error' => $this->validator->errors()
        ]);
        $stream = new Stream('php://memory', 'wb+');
        $stream->write($html);
        return $response
            ->withBody($stream)
            ->withHeader('Content-Type', 'text/html');
    }

    /**
     *
     */
    protected function formValid($request, $response, $next)
    {
        $user = $this->create($this->getAllRequestInput($request));
        $this->authenticator->login($request, $user, ModelBackend::class);
        $flash = $request->getAttribute('_messages');
        if (method_exists($flash, 'info')) {
            $flash->info('Welcome, registration completed');
        }
        return new RedirectResponse('/');
    }

    /**
     *
     */
    protected function create($input)
    {
        $user = new User;
        $user->name = $input['name'];
        $user->email = $input['email'];
        $user->date_joined = DateTime::now();
        $user->setPassword($input['password']);
        $user->save();

        return $user;
    }

    /**
     *
     */
    protected function validateRegister(Request $request)
    {
        return $this->isValid($request, [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:6|confirmed'
        ]);
    }
}
