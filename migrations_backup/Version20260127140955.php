<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260127140955 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE participation (id INT AUTO_INCREMENT NOT NULL, joined_at DATETIME NOT NULL, user_id_id INT DEFAULT NULL, event_id_id INT DEFAULT NULL, INDEX IDX_AB55E24F9D86650F (user_id_id), INDEX IDX_AB55E24F3E5F2F7B (event_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE participation ADD CONSTRAINT FK_AB55E24F9D86650F FOREIGN KEY (user_id_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE participation ADD CONSTRAINT FK_AB55E24F3E5F2F7B FOREIGN KEY (event_id_id) REFERENCES events (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE participation DROP FOREIGN KEY FK_AB55E24F9D86650F');
        $this->addSql('ALTER TABLE participation DROP FOREIGN KEY FK_AB55E24F3E5F2F7B');
        $this->addSql('DROP TABLE participation');
    }
}
