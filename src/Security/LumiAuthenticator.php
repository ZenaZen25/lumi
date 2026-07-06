<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LumiAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    public function __construct(private UrlGeneratorInterface $urlGenerator) {}

    public function authenticate(Request $request): Passport
    {
        // Récupère l'email saisi dans le formulaire de connexion.
        $email = $request->getPayload()->getString('_username');

        // Sauvegarde en session le type de connexion choisi :
        // "eleve" ou "admin" pour la partie Référent/Admin.
        $request->getSession()->set(
            'login_type',
            $request->request->get('login_type', 'eleve')
        );

        // Sauvegarde le dernier email saisi pour pouvoir le réafficher
        // dans le formulaire en cas d'erreur de connexion.
        $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $email);

        // Crée le passeport d'authentification Symfony :
        // utilisateur + mot de passe + protection CSRF + remember me.
        return new Passport(
            new UserBadge($email),
            new PasswordCredentials($request->getPayload()->getString('_password')),
            [
                new CsrfTokenBadge('authenticate', $request->getPayload()->getString('_csrf_token')),
                new RememberMeBadge(),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // Récupère l'utilisateur connecté et ses rôles Symfony.
        $user = $token->getUser();
        $roles = $user->getRoles();

        // Récupère le type de connexion choisi dans le formulaire.
        $loginType = $request->getSession()->get('login_type', 'eleve');

        // Si l'utilisateur choisit la connexion "Élève",
        // il doit obligatoirement avoir le rôle ROLE_ELEVE.
        // Sinon, on annule la session et on le renvoie vers la page de connexion.
        if ($loginType === 'eleve' && !in_array('ROLE_ELEVE', $roles, true)) {
            $request->getSession()->invalidate();

            return new RedirectResponse($this->urlGenerator->generate('app_login'));
        }

        // Si l'utilisateur choisit la connexion "Référent/Admin",
        // il doit avoir ROLE_REFERENT ou ROLE_ADMIN.
        // Sinon, on annule la session et on le renvoie vers la page de connexion.
        if ($loginType === 'admin' && !(
            in_array('ROLE_ADMIN', $roles, true)
            || in_array('ROLE_REFERENT', $roles, true)
        )) {
            $request->getSession()->invalidate();

            return new RedirectResponse($this->urlGenerator->generate('app_login'));
        }

        // Redirection automatique selon le rôle réel de l'utilisateur.
        // Un administrateur est envoyé vers le dashboard complet EasyAdmin.
        if (in_array('ROLE_ADMIN', $roles, true)) {
            return new RedirectResponse($this->urlGenerator->generate('admin'));
        }

        // Un référent est envoyé vers son espace dédié.
        if (in_array('ROLE_REFERENT', $roles, true)) {
            return new RedirectResponse($this->urlGenerator->generate('referent'));
        }

        // Un élève est envoyé vers sa page profil.
        if (in_array('ROLE_ELEVE', $roles, true)) {
            return new RedirectResponse($this->urlGenerator->generate('app_profil'));
        }

        // Sécurité par défaut : si aucun rôle reconnu,
        // l'utilisateur est renvoyé vers la page d'accueil.
        return new RedirectResponse($this->urlGenerator->generate('app_home'));
    }

    protected function getLoginUrl(Request $request): string
    {
        // Route utilisée par Symfony quand l'utilisateur doit se connecter.
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}