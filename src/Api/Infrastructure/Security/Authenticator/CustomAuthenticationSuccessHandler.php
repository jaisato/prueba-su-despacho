<?php

declare(strict_types=1);

namespace Api\Infrastructure\Security\Authenticator;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Lexik\Bundle\JWTAuthenticationBundle\Response\JWTAuthenticationSuccessResponse;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Cookie\JWTCookieProvider;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class CustomAuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    private $cookieProviders;
    protected $jwtManager;
    protected $dispatcher;
    protected $removeTokenFromBodyWhenCookiesUsed;

    /**
     * @param iterable|JWTCookieProvider[] $cookieProviders
     */
    public function __construct(JWTTokenManagerInterface $jwtManager, EventDispatcherInterface $dispatcher, iterable $cookieProviders = [], bool $removeTokenFromBodyWhenCookiesUsed = true)
    {
        $this->jwtManager                         = $jwtManager;
        $this->dispatcher                         = $dispatcher;
        $this->cookieProviders                    = $cookieProviders;
        $this->removeTokenFromBodyWhenCookiesUsed = $removeTokenFromBodyWhenCookiesUsed;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): Response
    {
        return $this->handleAuthenticationSuccess($token->getUser());
    }

    public function handleAuthenticationSuccess(UserInterface $user, $jwt = null): Response
    {
        if ($jwt === null) {
            $jwt = $this->jwtManager->create($user);
        }

        $jwtCookies = [];
        foreach ($this->cookieProviders as $cookieProvider) {
            $jwtCookies[] = $cookieProvider->createCookie($jwt);
        }

        $response = new JWTAuthenticationSuccessResponse($jwt, [], $jwtCookies);
        $event    = new AuthenticationSuccessEvent(['token' => $jwt], $user, $response);

        $this->dispatcher->dispatch($event, Events::AUTHENTICATION_SUCCESS);
        $responseData = $event->getData();

        if ($jwtCookies && $this->removeTokenFromBodyWhenCookiesUsed) {
            unset($responseData['token']);
        }

        if ($responseData) {
            if ($user->getUserIdentifier()) {
                $response->setData([
                    'user_id' => $user->getUserIdentifier(),
                    'token'   => $responseData['token'],
                ]);
            } else {
                $response->setData($responseData);
            }
        } else {
            $response->setStatusCode(JWTAuthenticationSuccessResponse::HTTP_NO_CONTENT);
        }

        return $response;
    }
}
