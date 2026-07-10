<?php

declare(strict_types=1);

namespace Tavp\Hub\Controllers;

use Tavp\Hub\HubController;
use Tavp\Tavpid\Auth\SessionAuth;
use Tavp\Tavpid\Auth\AuthService;
use Tavp\Tavpid\Auth\UserProvider;
use Tavp\Core\Http\Response;

/**
 * Admin auth — login/logout via OTP (delegates to tavpid).
 */
class AuthController extends HubController
{
    public function __construct(
        private ?SessionAuth $sessionAuth = null,
    ) {
        parent::__construct();
        $this->sessionAuth = $this->sessionAuth ?? $this->getSessionAuth();
    }

    public function showLogin(): string|Response
    {
        if ($this->sessionAuth?->check()) {
            return $this->redirect(config('hub.admin_prefix', '/admin'));
        }

        return $this->partial('hub::auth.login', [
            'error' => null,
            'brand' => config('hub.brand', 'TAVP Admin'),
        ]);
    }

    public function sendOtp(): Response
    {
        $email = (string) $this->request->input('email', '');

        if ($this->sessionAuth === null) {
            return $this->redirect(config('hub.admin_prefix', '/admin') . '/login');
        }

        $result = $this->sessionAuth->requestCode($email);

        if ($result === false) {
            return $this->partial('hub::auth.login', [
                'error' => 'That e-mail is not allowed to sign in.',
                'brand' => config('hub.brand', 'TAVP Admin'),
            ]);
        }

        return $this->redirect(config('hub.admin_prefix', '/admin') . '/verify');
    }

    public function showVerify(): string|Response
    {
        $identifier = $this->sessionAuth?->pendingIdentifier();

        if ($identifier === null) {
            return $this->redirect(config('hub.admin_prefix', '/admin') . '/login');
        }

        return $this->partial('hub::auth.verify', [
            'identifier' => $identifier,
            'error' => null,
            'brand' => config('hub.brand', 'TAVP Admin'),
        ]);
    }

    public function verify(): string|Response
    {
        $code = (string) $this->request->input('code', '');

        if ($this->sessionAuth === null || !$this->sessionAuth->verify($code)) {
            return $this->partial('hub::auth.verify', [
                'identifier' => $this->sessionAuth?->pendingIdentifier() ?? '',
                'error' => 'Invalid or expired code. Please try again.',
                'brand' => config('hub.brand', 'TAVP Admin'),
            ]);
        }

        return $this->redirect(config('hub.admin_prefix', '/admin'));
    }

    public function logout(): Response
    {
        $this->sessionAuth?->logout();

        return $this->redirect(config('hub.admin_prefix', '/admin') . '/login');
    }
}
