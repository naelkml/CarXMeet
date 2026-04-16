<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260416133956 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE convoy_participation (id INT AUTO_INCREMENT NOT NULL, joined_at DATETIME NOT NULL, convoy_id_id INT NOT NULL, user_id_id INT NOT NULL, INDEX IDX_883675D29F276072 (convoy_id_id), INDEX IDX_883675D29D86650F (user_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE convoy_participation ADD CONSTRAINT FK_883675D29F276072 FOREIGN KEY (convoy_id_id) REFERENCES convoy (id)');
        $this->addSql('ALTER TABLE convoy_participation ADD CONSTRAINT FK_883675D29D86650F FOREIGN KEY (user_id_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE convoy_participation ADD CONSTRAINT uniq_convoy_user UNIQUE (convoy_id_id, user_id_id)');
        $this->addSql('ALTER TABLE convoy ADD departure_date VARCHAR(10) DEFAULT NULL, CHANGE participants participants VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD profile_photo LONGBLOB DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE convoy_participation DROP FOREIGN KEY FK_883675D29F276072');
        $this->addSql('ALTER TABLE convoy_participation DROP FOREIGN KEY FK_883675D29D86650F');
        $this->addSql('DROP TABLE convoy_participation');
        $this->addSql('ALTER TABLE convoy DROP departure_date, CHANGE participants participants VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE user DROP profile_photo');
    }
}
