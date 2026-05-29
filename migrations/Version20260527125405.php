<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260527125405 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE alerte (id INT AUTO_INCREMENT NOT NULL, severite VARCHAR(20) NOT NULL, statut VARCHAR(20) DEFAULT NULL, note_interne LONGTEXT DEFAULT NULL, created_at DATETIME DEFAULT NULL, treated_at DATETIME DEFAULT NULL, signalement_id INT NOT NULL, treated_by_id INT DEFAULT NULL, INDEX IDX_3AE753A65C5E57E (signalement_id), INDEX IDX_3AE753A794E2304 (treated_by_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE badge (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) NOT NULL, description VARCHAR(255) DEFAULT NULL, icone VARCHAR(100) DEFAULT NULL, points_requis INT DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE conversation (id INT AUTO_INCREMENT NOT NULL, session_token VARCHAR(64) DEFAULT NULL, started_at DATETIME DEFAULT NULL, ended_at DATETIME DEFAULT NULL, user_id INT DEFAULT NULL, signalement_id INT DEFAULT NULL, INDEX IDX_8A8E26E9A76ED395 (user_id), INDEX IDX_8A8E26E965C5E57E (signalement_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE courage_point (id INT AUTO_INCREMENT NOT NULL, points INT NOT NULL, raison VARCHAR(50) NOT NULL, created_at DATETIME DEFAULT NULL, user_id INT NOT NULL, INDEX IDX_5FA54963A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE etablissement (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(150) NOT NULL, code_uai VARCHAR(20) DEFAULT NULL, email_contact VARCHAR(150) DEFAULT NULL, created_at DATETIME DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE heatmap_zone (id INT AUTO_INCREMENT NOT NULL, nom_zone VARCHAR(100) NOT NULL, position_x INT DEFAULT NULL, position_y INT DEFAULT NULL, incident_count INT DEFAULT NULL, last_updated DATETIME DEFAULT NULL, etablissement_id INT NOT NULL, INDEX IDX_B183A38FFF631228 (etablissement_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE message (id INT AUTO_INCREMENT NOT NULL, role VARCHAR(10) NOT NULL, content LONGTEXT NOT NULL, created_at DATETIME NOT NULL, conversation_id INT NOT NULL, INDEX IDX_B6BD307F9AC0396 (conversation_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE signalement (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(50) NOT NULL, zone VARCHAR(100) DEFAULT NULL, severite VARCHAR(20) NOT NULL, description LONGTEXT DEFAULT NULL, est_recurrent TINYINT DEFAULT NULL, statut VARCHAR(20) NOT NULL, anonymous_token VARCHAR(64) DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, user_id INT DEFAULT NULL, etablissement_id INT NOT NULL, INDEX IDX_F4B55114A76ED395 (user_id), INDEX IDX_F4B55114FF631228 (etablissement_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, prenom VARCHAR(50) DEFAULT NULL, code_classe VARCHAR(20) DEFAULT NULL, password VARCHAR(255) DEFAULT NULL, role VARCHAR(20) NOT NULL, is_anonymous TINYINT DEFAULT NULL, created_at DATETIME DEFAULT NULL, last_login DATETIME DEFAULT NULL, session_token VARCHAR(255) DEFAULT NULL, etablissement_id INT DEFAULT NULL, INDEX IDX_8D93D649FF631228 (etablissement_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user_badge (id INT AUTO_INCREMENT NOT NULL, obtained_at DATETIME DEFAULT NULL, user_id INT NOT NULL, badge_id INT NOT NULL, INDEX IDX_1C32B345A76ED395 (user_id), INDEX IDX_1C32B345F7A2C2FC (badge_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE alerte ADD CONSTRAINT FK_3AE753A65C5E57E FOREIGN KEY (signalement_id) REFERENCES signalement (id)');
        $this->addSql('ALTER TABLE alerte ADD CONSTRAINT FK_3AE753A794E2304 FOREIGN KEY (treated_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE conversation ADD CONSTRAINT FK_8A8E26E9A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE conversation ADD CONSTRAINT FK_8A8E26E965C5E57E FOREIGN KEY (signalement_id) REFERENCES signalement (id)');
        $this->addSql('ALTER TABLE courage_point ADD CONSTRAINT FK_5FA54963A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE heatmap_zone ADD CONSTRAINT FK_B183A38FFF631228 FOREIGN KEY (etablissement_id) REFERENCES etablissement (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F9AC0396 FOREIGN KEY (conversation_id) REFERENCES conversation (id)');
        $this->addSql('ALTER TABLE signalement ADD CONSTRAINT FK_F4B55114A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE signalement ADD CONSTRAINT FK_F4B55114FF631228 FOREIGN KEY (etablissement_id) REFERENCES etablissement (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649FF631228 FOREIGN KEY (etablissement_id) REFERENCES etablissement (id)');
        $this->addSql('ALTER TABLE user_badge ADD CONSTRAINT FK_1C32B345A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_badge ADD CONSTRAINT FK_1C32B345F7A2C2FC FOREIGN KEY (badge_id) REFERENCES badge (id)');
        $this->addSql('DROP TABLE alertes');
        $this->addSql('DROP TABLE badges');
        $this->addSql('DROP TABLE conversations');
        $this->addSql('DROP TABLE courage_points');
        $this->addSql('DROP TABLE etablissements');
        $this->addSql('DROP TABLE heatmap_zones');
        $this->addSql('DROP TABLE messages');
        $this->addSql('DROP TABLE signalements');
        $this->addSql('DROP TABLE user_badges');
        $this->addSql('DROP TABLE users');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE alertes (id INT UNSIGNED AUTO_INCREMENT NOT NULL, signalement_id INT UNSIGNED NOT NULL, severite ENUM(\'faible\', \'moderee\', \'critique\') CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, statut ENUM(\'nouvelle\', \'vue\', \'traitee\') CHARACTER SET utf8mb4 DEFAULT \'nouvelle\' COLLATE `utf8mb4_unicode_ci`, note_interne TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, treated_by INT UNSIGNED DEFAULT NULL, treated_at DATETIME DEFAULT NULL, INDEX signalement_id (signalement_id), INDEX treated_by (treated_by), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = MyISAM COMMENT = \'\' ');
        $this->addSql('CREATE TABLE badges (id INT UNSIGNED AUTO_INCREMENT NOT NULL, nom VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, description VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, icone VARCHAR(100) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, points_requis INT DEFAULT 0, condition_type ENUM(\'points\', \'signalement\', \'conversation\', \'streak\') CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = MyISAM COMMENT = \'\' ');
        $this->addSql('CREATE TABLE conversations (id INT UNSIGNED AUTO_INCREMENT NOT NULL, user_id INT UNSIGNED DEFAULT NULL, session_token VARCHAR(64) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, signalement_id INT UNSIGNED DEFAULT NULL, started_at DATETIME DEFAULT CURRENT_TIMESTAMP, ended_at DATETIME DEFAULT NULL, INDEX signalement_id (signalement_id), INDEX user_id (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = MyISAM COMMENT = \'\' ');
        $this->addSql('CREATE TABLE courage_points (id INT UNSIGNED AUTO_INCREMENT NOT NULL, user_id INT UNSIGNED NOT NULL, points INT NOT NULL, raison ENUM(\'conversation\', \'signalement\', \'badge\', \'streak\', \'bonus\') CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, INDEX user_id (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = MyISAM COMMENT = \'\' ');
        $this->addSql('CREATE TABLE etablissements (id INT UNSIGNED AUTO_INCREMENT NOT NULL, nom VARCHAR(150) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, code_uai VARCHAR(20) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, adresse TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, email_contact VARCHAR(150) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, UNIQUE INDEX code_uai (code_uai), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = MyISAM COMMENT = \'\' ');
        $this->addSql('CREATE TABLE heatmap_zones (id INT UNSIGNED AUTO_INCREMENT NOT NULL, etablissement_id INT UNSIGNED NOT NULL, nom_zone VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, position_x INT DEFAULT NULL, position_y INT DEFAULT NULL, incident_count INT DEFAULT 0, last_updated DATETIME DEFAULT NULL, INDEX etablissement_id (etablissement_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = MyISAM COMMENT = \'\' ');
        $this->addSql('CREATE TABLE messages (id INT UNSIGNED AUTO_INCREMENT NOT NULL, conversation_id INT UNSIGNED NOT NULL, role ENUM(\'user\', \'assistant\') CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, content TEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, INDEX conversation_id (conversation_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = MyISAM COMMENT = \'\' ');
        $this->addSql('CREATE TABLE signalements (id INT UNSIGNED AUTO_INCREMENT NOT NULL, user_id INT UNSIGNED DEFAULT NULL, etablissement_id INT UNSIGNED NOT NULL, type ENUM(\'verbal\', \'physique\', \'cyber\', \'exclusion\', \'autre\') CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, zone VARCHAR(100) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, severite ENUM(\'faible\', \'modere\', \'eleve\', \'critique\') CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, description TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, est_recurrent TINYINT DEFAULT 0, statut ENUM(\'nouveau\', \'en_cours\', \'resolu\') CHARACTER SET utf8mb4 DEFAULT \'nouveau\' COLLATE `utf8mb4_unicode_ci`, anonymous_token VARCHAR(64) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME DEFAULT NULL, INDEX etablissement_id (etablissement_id), INDEX user_id (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = MyISAM COMMENT = \'\' ');
        $this->addSql('CREATE TABLE user_badges (id INT UNSIGNED AUTO_INCREMENT NOT NULL, user_id INT UNSIGNED NOT NULL, badge_id INT UNSIGNED NOT NULL, obtained_at DATETIME DEFAULT CURRENT_TIMESTAMP, INDEX badge_id (badge_id), UNIQUE INDEX unique_user_badge (user_id, badge_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = MyISAM COMMENT = \'\' ');
        $this->addSql('CREATE TABLE users (id INT UNSIGNED AUTO_INCREMENT NOT NULL, prenom VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, code_classe VARCHAR(20) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, password VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, role ENUM(\'eleve\', \'admin\', \'referent\') CHARACTER SET utf8mb4 DEFAULT \'eleve\' COLLATE `utf8mb4_unicode_ci`, etablissement_id INT UNSIGNED DEFAULT NULL, is_anonymous TINYINT DEFAULT 1, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, last_login DATETIME DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = MyISAM COMMENT = \'\' ');
        $this->addSql('ALTER TABLE alerte DROP FOREIGN KEY FK_3AE753A65C5E57E');
        $this->addSql('ALTER TABLE alerte DROP FOREIGN KEY FK_3AE753A794E2304');
        $this->addSql('ALTER TABLE conversation DROP FOREIGN KEY FK_8A8E26E9A76ED395');
        $this->addSql('ALTER TABLE conversation DROP FOREIGN KEY FK_8A8E26E965C5E57E');
        $this->addSql('ALTER TABLE courage_point DROP FOREIGN KEY FK_5FA54963A76ED395');
        $this->addSql('ALTER TABLE heatmap_zone DROP FOREIGN KEY FK_B183A38FFF631228');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F9AC0396');
        $this->addSql('ALTER TABLE signalement DROP FOREIGN KEY FK_F4B55114A76ED395');
        $this->addSql('ALTER TABLE signalement DROP FOREIGN KEY FK_F4B55114FF631228');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649FF631228');
        $this->addSql('ALTER TABLE user_badge DROP FOREIGN KEY FK_1C32B345A76ED395');
        $this->addSql('ALTER TABLE user_badge DROP FOREIGN KEY FK_1C32B345F7A2C2FC');
        $this->addSql('DROP TABLE alerte');
        $this->addSql('DROP TABLE badge');
        $this->addSql('DROP TABLE conversation');
        $this->addSql('DROP TABLE courage_point');
        $this->addSql('DROP TABLE etablissement');
        $this->addSql('DROP TABLE heatmap_zone');
        $this->addSql('DROP TABLE message');
        $this->addSql('DROP TABLE signalement');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_badge');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
