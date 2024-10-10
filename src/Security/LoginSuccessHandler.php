<?php
namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
private $router;
private $security;

public function __construct(RouterInterface $router, Security $security)
{
$this->router = $router;
$this->security = $security;
}

public function onAuthenticationSuccess(Request $request, TokenInterface $token): Response
{
// Récupère l'utilisateur connecté
$user = $this->security->getUser();

// Redirection selon les rôles de l'utilisateur
if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
return new RedirectResponse($this->router->generate('app_admin')); // Page admin
} elseif (in_array('ROLE_USER', $user->getRoles(), true)) {
return new RedirectResponse($this->router->generate('app_user')); // Page utilisateur
}

// Redirection par défaut si aucun rôle ne correspond
return new RedirectResponse($this->router->generate('app_login'));
}
}
