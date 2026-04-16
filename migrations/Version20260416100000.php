<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260416100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Vehicle details + galleries (vehicles/events) and per-user event ratings';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE vehicle ADD description LONGTEXT DEFAULT NULL');

        $this->addSql('CREATE TABLE vehicle_photo (id INT AUTO_INCREMENT NOT NULL, vehicle_id_id INT NOT NULL, photo LONGBLOB NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_5F1E7A1A1B80E486 (vehicle_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE vehicle_photo ADD CONSTRAINT FK_5F1E7A1A1B80E486 FOREIGN KEY (vehicle_id_id) REFERENCES vehicle (id) ON DELETE CASCADE');

        $this->addSql('ALTER TABLE events CHANGE cover_photo cover_photo LONGBLOB DEFAULT NULL');
        $this->addSql('ALTER TABLE events CHANGE gallery gallery VARCHAR(255) DEFAULT NULL');

        $this->addSql('CREATE TABLE event_photo (id INT AUTO_INCREMENT NOT NULL, event_id_id INT NOT NULL, photo LONGBLOB NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_5A8DA7D03E5F2F7B (event_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE event_photo ADD CONSTRAINT FK_5A8DA7D03E5F2F7B FOREIGN KEY (event_id_id) REFERENCES events (id) ON DELETE CASCADE');

        $this->addSql('CREATE TABLE event_rating (id INT AUTO_INCREMENT NOT NULL, event_id_id INT NOT NULL, user_id_id INT NOT NULL, rating INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX uniq_event_user (event_id_id, user_id_id), INDEX IDX_C49C43E33E5F2F7B (event_id_id), INDEX IDX_C49C43E39D86650F (user_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE event_rating ADD CONSTRAINT FK_C49C43E33E5F2F7B FOREIGN KEY (event_id_id) REFERENCES events (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE event_rating ADD CONSTRAINT FK_C49C43E39D86650F FOREIGN KEY (user_id_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE vehicle_photo DROP FOREIGN KEY FK_5F1E7A1A1B80E486');
        $this->addSql('DROP TABLE vehicle_photo');
        $this->addSql('ALTER TABLE vehicle DROP description');

        $this->addSql('ALTER TABLE event_photo DROP FOREIGN KEY FK_5A8DA7D03E5F2F7B');
        $this->addSql('DROP TABLE event_photo');

        $this->addSql('ALTER TABLE event_rating DROP FOREIGN KEY FK_C49C43E33E5F2F7B');
        $this->addSql('ALTER TABLE event_rating DROP FOREIGN KEY FK_C49C43E39D86650F');
        $this->addSql('DROP TABLE event_rating');

        $this->addSql('ALTER TABLE events CHANGE cover_photo cover_photo VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE events CHANGE gallery gallery VARCHAR(255) NOT NULL');
    }
}

