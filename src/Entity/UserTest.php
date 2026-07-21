<?php

namespace App\Tests\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
{
    // Vérifie qu'un utilisateur peut être configuré correctement
    public function testUserCanBeConfigured(): void
    {
        $user = new User();

        // Dates utilisées pour le test
        $createdAt = new \DateTimeImmutable('2026-01-15 10:00:00');
        $lastLogin = new \DateTime('2026-01-16 09:30:00');

        // Remplit les principales informations de l'utilisateur
        $result = $user
            ->setEmail('eleve@example.com')
            ->setPrenom('Lina')
            ->setCodeClasse('BTS-SIO')
            ->setPassword('mot-de-passe-hashe')
            ->setRole('eleve')
            ->setIsAnonymous(false)
            ->setCreatedAt($createdAt)
            ->setLastLogin($lastLogin)
            ->setSessionToken('session-token-test');

        // Vérifie que les setters retournent bien le même objet
        self::assertSame($user, $result);

        // Vérifie que chaque valeur a bien été enregistrée
        self::assertSame('eleve@example.com', $user->getEmail());
        self::assertSame('Lina', $user->getPrenom());
        self::assertSame('BTS-SIO', $user->getCodeClasse());
        self::assertSame('mot-de-passe-hashe', $user->getPassword());
        self::assertSame('eleve', $user->getRole());
        self::assertFalse($user->isAnonymous());
        self::assertSame($createdAt, $user->getCreatedAt());
        self::assertSame($lastLogin, $user->getLastLogin());
        self::assertSame('session-token-test', $user->getSessionToken());
    }

    // Vérifie que l'identifiant de connexion correspond à l'adresse e-mail
    public function testUserIdentifierIsEmail(): void
    {
        $user = new User();
        $user->setEmail('zineb@example.com');

        self::assertSame('zineb@example.com', $user->getUserIdentifier());
    }

    // Vérifie que le rôle métier est transformé au format attendu par Symfony
    public function testRoleIsConvertedToSymfonyRole(): void
    {
        $user = new User();
        $user->setRole('referent');

        self::assertSame(['ROLE_REFERENT'], $user->getRoles());
    }

    // Vérifie que le rôle par défaut est ROLE_ELEVE
    public function testDefaultRoleIsEleveWhenRoleIsMissing(): void
    {
        $user = new User();

        self::assertSame(['ROLE_ELEVE'], $user->getRoles());
    }

    // Vérifie que le prénom est utilisé pour afficher l'utilisateur
    public function testToStringUsesPrenomFirst(): void
    {
        $user = new User();
        $user
            ->setEmail('eleve@example.com')
            ->setPrenom('Nora');

        self::assertSame('Nora', (string) $user);
    }

    // Vérifie que l'e-mail est utilisé si le prénom est absent
    public function testToStringUsesEmailWhenPrenomIsMissing(): void
    {
        $user = new User();
        $user->setEmail('eleve@example.com');

        self::assertSame('eleve@example.com', (string) $user);
    }

    // Vérifie que toutes les collections sont vides lors de la création
    public function testCollectionsAreInitiallyEmpty(): void
    {
        $user = new User();

        self::assertCount(0, $user->getSignalements());
        self::assertCount(0, $user->getConversations());
        self::assertCount(0, $user->getUserBadges());
        self::assertCount(0, $user->getCouragePoints());
        self::assertCount(0, $user->getAlertesTraitees());
    }
}