<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260127140442 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE adresses (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, photo VARCHAR(255) NOT NULL, website_url VARCHAR(255) NOT NULL, region_id_id INT DEFAULT NULL, INDEX IDX_EF192552C7209D4F (region_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE convoy (id INT AUTO_INCREMENT NOT NULL, departure_location VARCHAR(255) NOT NULL, departure_time VARCHAR(255) NOT NULL, participants VARCHAR(255) NOT NULL, event_id_id INT NOT NULL, INDEX IDX_64F916E93E5F2F7B (event_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE events (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, cover_photo VARCHAR(255) NOT NULL, gallery VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, date VARCHAR(255) NOT NULL, location VARCHAR(255) NOT NULL, rating_average VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, region_id_id INT DEFAULT NULL, INDEX IDX_5387574AC7209D4F (region_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE region (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE adresses ADD CONSTRAINT FK_EF192552C7209D4F FOREIGN KEY (region_id_id) REFERENCES region (id)');
        $this->addSql('ALTER TABLE convoy ADD CONSTRAINT FK_64F916E93E5F2F7B FOREIGN KEY (event_id_id) REFERENCES events (id)');
        $this->addSql('ALTER TABLE events ADD CONSTRAINT FK_5387574AC7209D4F FOREIGN KEY (region_id_id) REFERENCES region (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE adresses DROP FOREIGN KEY FK_EF192552C7209D4F');
        $this->addSql('ALTER TABLE convoy DROP FOREIGN KEY FK_64F916E93E5F2F7B');
        $this->addSql('ALTER TABLE events DROP FOREIGN KEY FK_5387574AC7209D4F');
        $this->addSql('DROP TABLE adresses');
        $this->addSql('DROP TABLE convoy');
        $this->addSql('DROP TABLE events');
        $this->addSql('DROP TABLE region');
    }
}
