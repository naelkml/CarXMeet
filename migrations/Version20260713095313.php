<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260713095313 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE events ADD organisateur_id INT NOT NULL, DROP organisateur');
        $this->addSql('ALTER TABLE events ADD CONSTRAINT FK_5387574AD936B2FA FOREIGN KEY (organisateur_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_5387574AD936B2FA ON events (organisateur_id)');
        $this->addSql('ALTER TABLE user ADD is_verified TINYINT NOT NULL, ADD confirmation_token VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE events DROP FOREIGN KEY FK_5387574AD936B2FA');
        $this->addSql('DROP INDEX IDX_5387574AD936B2FA ON events');
        $this->addSql('ALTER TABLE events ADD organisateur VARCHAR(255) NOT NULL, DROP organisateur_id');
        $this->addSql('ALTER TABLE user DROP is_verified, DROP confirmation_token');
    }
}
