<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260617142627 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE conversation ADD signalement_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE conversation ADD CONSTRAINT FK_8A8E26E965C5E57E FOREIGN KEY (signalement_id) REFERENCES signalement (id)');
        $this->addSql('CREATE INDEX IDX_8A8E26E965C5E57E ON conversation (signalement_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE conversation DROP FOREIGN KEY FK_8A8E26E965C5E57E');
        $this->addSql('DROP INDEX IDX_8A8E26E965C5E57E ON conversation');
        $this->addSql('ALTER TABLE conversation DROP signalement_id');
    }
}
