<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260601131528 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE conversation DROP FOREIGN KEY `FK_8A8E26E965C5E57E`');
        $this->addSql('DROP INDEX IDX_8A8E26E965C5E57E ON conversation');
        $this->addSql('ALTER TABLE conversation ADD created_at DATETIME DEFAULT NULL, ADD updated_at DATETIME DEFAULT NULL, ADD has_alert TINYINT DEFAULT NULL, DROP session_token, DROP started_at, DROP ended_at, DROP signalement_id');
        $this->addSql('ALTER TABLE message ADD is_alert TINYINT DEFAULT NULL, CHANGE created_at created_at DATETIME DEFAULT NULL, CHANGE role sender VARCHAR(10) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE conversation ADD session_token VARCHAR(64) DEFAULT NULL, ADD started_at DATETIME DEFAULT NULL, ADD ended_at DATETIME DEFAULT NULL, ADD signalement_id INT DEFAULT NULL, DROP created_at, DROP updated_at, DROP has_alert');
        $this->addSql('ALTER TABLE conversation ADD CONSTRAINT `FK_8A8E26E965C5E57E` FOREIGN KEY (signalement_id) REFERENCES signalement (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_8A8E26E965C5E57E ON conversation (signalement_id)');
        $this->addSql('ALTER TABLE message DROP is_alert, CHANGE created_at created_at DATETIME NOT NULL, CHANGE sender role VARCHAR(10) NOT NULL');
    }
}
