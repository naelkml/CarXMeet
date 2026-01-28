<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260127133610 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE crew (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, logo VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, created_by_id INT NOT NULL, UNIQUE INDEX UNIQ_894940B2B03A8386 (created_by_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE crew_user (crew_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_B4AF30565FE259F6 (crew_id), INDEX IDX_B4AF3056A76ED395 (user_id), PRIMARY KEY (crew_id, user_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE friendship (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(255) NOT NULL, requester_id_id INT NOT NULL, receiver_id_id INT NOT NULL, INDEX IDX_7234A45F9C0CF0F6 (requester_id_id), INDEX IDX_7234A45FBE20CAB0 (receiver_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, username VARCHAR(255) NOT NULL, phone VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, snapchat VARCHAR(255) DEFAULT NULL, instagram VARCHAR(255) DEFAULT NULL, twitter VARCHAR(255) DEFAULT NULL, tiktok VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE crew ADD CONSTRAINT FK_894940B2B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE crew_user ADD CONSTRAINT FK_B4AF30565FE259F6 FOREIGN KEY (crew_id) REFERENCES crew (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE crew_user ADD CONSTRAINT FK_B4AF3056A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE friendship ADD CONSTRAINT FK_7234A45F9C0CF0F6 FOREIGN KEY (requester_id_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE friendship ADD CONSTRAINT FK_7234A45FBE20CAB0 FOREIGN KEY (receiver_id_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE crew DROP FOREIGN KEY FK_894940B2B03A8386');
        $this->addSql('ALTER TABLE crew_user DROP FOREIGN KEY FK_B4AF30565FE259F6');
        $this->addSql('ALTER TABLE crew_user DROP FOREIGN KEY FK_B4AF3056A76ED395');
        $this->addSql('ALTER TABLE friendship DROP FOREIGN KEY FK_7234A45F9C0CF0F6');
        $this->addSql('ALTER TABLE friendship DROP FOREIGN KEY FK_7234A45FBE20CAB0');
        $this->addSql('DROP TABLE crew');
        $this->addSql('DROP TABLE crew_user');
        $this->addSql('DROP TABLE friendship');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
