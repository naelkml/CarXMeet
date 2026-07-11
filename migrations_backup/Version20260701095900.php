<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260701095900 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE adresses (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, photo VARCHAR(255) NOT NULL, website_url VARCHAR(255) NOT NULL, region_id_id INT DEFAULT NULL, INDEX IDX_EF192552C7209D4F (region_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE articles (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, cover_photo VARCHAR(255) NOT NULL, summary VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, created_at DATETIME NOT NULL, user_id_id INT DEFAULT NULL, INDEX IDX_BFDD31689D86650F (user_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE convoy (id INT AUTO_INCREMENT NOT NULL, departure_location VARCHAR(255) NOT NULL, departure_time VARCHAR(255) NOT NULL, departure_date VARCHAR(10) DEFAULT NULL, participants VARCHAR(255) DEFAULT NULL, event_id_id INT NOT NULL, INDEX IDX_64F916E93E5F2F7B (event_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE convoy_participation (id INT AUTO_INCREMENT NOT NULL, joined_at DATETIME NOT NULL, convoy_id_id INT NOT NULL, user_id_id INT NOT NULL, INDEX IDX_883675D29F276072 (convoy_id_id), INDEX IDX_883675D29D86650F (user_id_id), UNIQUE INDEX uniq_convoy_user (convoy_id_id, user_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE crew (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, logo VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, created_by_id INT NOT NULL, UNIQUE INDEX UNIQ_894940B2B03A8386 (created_by_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE crew_user (crew_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_B4AF30565FE259F6 (crew_id), INDEX IDX_B4AF3056A76ED395 (user_id), PRIMARY KEY (crew_id, user_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE event_photo (id INT AUTO_INCREMENT NOT NULL, photo LONGBLOB NOT NULL, created_at DATETIME NOT NULL, event_id_id INT NOT NULL, INDEX IDX_55AC35343E5F2F7B (event_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE event_rating (id INT AUTO_INCREMENT NOT NULL, rating INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, event_id_id INT NOT NULL, user_id_id INT NOT NULL, INDEX IDX_EA1051703E5F2F7B (event_id_id), INDEX IDX_EA1051709D86650F (user_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE events (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, cover_photo LONGBLOB DEFAULT NULL, gallery VARCHAR(255) DEFAULT NULL, type VARCHAR(255) NOT NULL, date VARCHAR(255) NOT NULL, location VARCHAR(255) NOT NULL, rating_average VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, organisateur_id INT NOT NULL, region_id_id INT DEFAULT NULL, INDEX IDX_5387574AD936B2FA (organisateur_id), INDEX IDX_5387574AC7209D4F (region_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE friendship (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(255) NOT NULL, requester_id_id INT NOT NULL, receiver_id_id INT NOT NULL, INDEX IDX_7234A45F9C0CF0F6 (requester_id_id), INDEX IDX_7234A45FBE20CAB0 (receiver_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE participation (id INT AUTO_INCREMENT NOT NULL, joined_at DATETIME NOT NULL, user_id_id INT DEFAULT NULL, event_id_id INT DEFAULT NULL, INDEX IDX_AB55E24F9D86650F (user_id_id), INDEX IDX_AB55E24F3E5F2F7B (event_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE postphoto (id INT AUTO_INCREMENT NOT NULL, image VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, user_id_id INT DEFAULT NULL, INDEX IDX_80C793089D86650F (user_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE region (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user (roles JSON NOT NULL, password VARCHAR(255) NOT NULL, id INT AUTO_INCREMENT NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, username VARCHAR(255) NOT NULL, phone VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, snapchat VARCHAR(255) DEFAULT NULL, instagram VARCHAR(255) DEFAULT NULL, twitter VARCHAR(255) DEFAULT NULL, tiktok VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, is_verified TINYINT NOT NULL, confirmation_token VARCHAR(255) DEFAULT NULL, profile_photo LONGBLOB DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE vehicle (id INT AUTO_INCREMENT NOT NULL, brand VARCHAR(255) NOT NULL, model VARCHAR(255) NOT NULL, year VARCHAR(4) NOT NULL, engine VARCHAR(255) NOT NULL, preparation VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, photos LONGBLOB DEFAULT NULL, user_id_id INT DEFAULT NULL, INDEX IDX_1B80E4869D86650F (user_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE vehicle_photo (id INT AUTO_INCREMENT NOT NULL, photo LONGBLOB NOT NULL, created_at DATETIME NOT NULL, vehicle_id_id INT NOT NULL, INDEX IDX_761804F41DEB1EBB (vehicle_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE adresses ADD CONSTRAINT FK_EF192552C7209D4F FOREIGN KEY (region_id_id) REFERENCES region (id)');
        $this->addSql('ALTER TABLE articles ADD CONSTRAINT FK_BFDD31689D86650F FOREIGN KEY (user_id_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE convoy ADD CONSTRAINT FK_64F916E93E5F2F7B FOREIGN KEY (event_id_id) REFERENCES events (id)');
        $this->addSql('ALTER TABLE convoy_participation ADD CONSTRAINT FK_883675D29F276072 FOREIGN KEY (convoy_id_id) REFERENCES convoy (id)');
        $this->addSql('ALTER TABLE convoy_participation ADD CONSTRAINT FK_883675D29D86650F FOREIGN KEY (user_id_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE crew ADD CONSTRAINT FK_894940B2B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE crew_user ADD CONSTRAINT FK_B4AF30565FE259F6 FOREIGN KEY (crew_id) REFERENCES crew (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE crew_user ADD CONSTRAINT FK_B4AF3056A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE event_photo ADD CONSTRAINT FK_55AC35343E5F2F7B FOREIGN KEY (event_id_id) REFERENCES events (id)');
        $this->addSql('ALTER TABLE event_rating ADD CONSTRAINT FK_EA1051703E5F2F7B FOREIGN KEY (event_id_id) REFERENCES events (id)');
        $this->addSql('ALTER TABLE event_rating ADD CONSTRAINT FK_EA1051709D86650F FOREIGN KEY (user_id_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE events ADD CONSTRAINT FK_5387574AD936B2FA FOREIGN KEY (organisateur_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE events ADD CONSTRAINT FK_5387574AC7209D4F FOREIGN KEY (region_id_id) REFERENCES region (id)');
        $this->addSql('ALTER TABLE friendship ADD CONSTRAINT FK_7234A45F9C0CF0F6 FOREIGN KEY (requester_id_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE friendship ADD CONSTRAINT FK_7234A45FBE20CAB0 FOREIGN KEY (receiver_id_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE participation ADD CONSTRAINT FK_AB55E24F9D86650F FOREIGN KEY (user_id_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE participation ADD CONSTRAINT FK_AB55E24F3E5F2F7B FOREIGN KEY (event_id_id) REFERENCES events (id)');
        $this->addSql('ALTER TABLE postphoto ADD CONSTRAINT FK_80C793089D86650F FOREIGN KEY (user_id_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE vehicle ADD CONSTRAINT FK_1B80E4869D86650F FOREIGN KEY (user_id_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE vehicle_photo ADD CONSTRAINT FK_761804F41DEB1EBB FOREIGN KEY (vehicle_id_id) REFERENCES vehicle (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE adresses DROP FOREIGN KEY FK_EF192552C7209D4F');
        $this->addSql('ALTER TABLE articles DROP FOREIGN KEY FK_BFDD31689D86650F');
        $this->addSql('ALTER TABLE convoy DROP FOREIGN KEY FK_64F916E93E5F2F7B');
        $this->addSql('ALTER TABLE convoy_participation DROP FOREIGN KEY FK_883675D29F276072');
        $this->addSql('ALTER TABLE convoy_participation DROP FOREIGN KEY FK_883675D29D86650F');
        $this->addSql('ALTER TABLE crew DROP FOREIGN KEY FK_894940B2B03A8386');
        $this->addSql('ALTER TABLE crew_user DROP FOREIGN KEY FK_B4AF30565FE259F6');
        $this->addSql('ALTER TABLE crew_user DROP FOREIGN KEY FK_B4AF3056A76ED395');
        $this->addSql('ALTER TABLE event_photo DROP FOREIGN KEY FK_55AC35343E5F2F7B');
        $this->addSql('ALTER TABLE event_rating DROP FOREIGN KEY FK_EA1051703E5F2F7B');
        $this->addSql('ALTER TABLE event_rating DROP FOREIGN KEY FK_EA1051709D86650F');
        $this->addSql('ALTER TABLE events DROP FOREIGN KEY FK_5387574AD936B2FA');
        $this->addSql('ALTER TABLE events DROP FOREIGN KEY FK_5387574AC7209D4F');
        $this->addSql('ALTER TABLE friendship DROP FOREIGN KEY FK_7234A45F9C0CF0F6');
        $this->addSql('ALTER TABLE friendship DROP FOREIGN KEY FK_7234A45FBE20CAB0');
        $this->addSql('ALTER TABLE participation DROP FOREIGN KEY FK_AB55E24F9D86650F');
        $this->addSql('ALTER TABLE participation DROP FOREIGN KEY FK_AB55E24F3E5F2F7B');
        $this->addSql('ALTER TABLE postphoto DROP FOREIGN KEY FK_80C793089D86650F');
        $this->addSql('ALTER TABLE vehicle DROP FOREIGN KEY FK_1B80E4869D86650F');
        $this->addSql('ALTER TABLE vehicle_photo DROP FOREIGN KEY FK_761804F41DEB1EBB');
        $this->addSql('DROP TABLE adresses');
        $this->addSql('DROP TABLE articles');
        $this->addSql('DROP TABLE convoy');
        $this->addSql('DROP TABLE convoy_participation');
        $this->addSql('DROP TABLE crew');
        $this->addSql('DROP TABLE crew_user');
        $this->addSql('DROP TABLE event_photo');
        $this->addSql('DROP TABLE event_rating');
        $this->addSql('DROP TABLE events');
        $this->addSql('DROP TABLE friendship');
        $this->addSql('DROP TABLE participation');
        $this->addSql('DROP TABLE postphoto');
        $this->addSql('DROP TABLE region');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE vehicle');
        $this->addSql('DROP TABLE vehicle_photo');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
