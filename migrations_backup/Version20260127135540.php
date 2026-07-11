<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260127135540 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE postphoto (id INT AUTO_INCREMENT NOT NULL, image VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, user_id_id INT DEFAULT NULL, INDEX IDX_80C793089D86650F (user_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE vehicle (id INT AUTO_INCREMENT NOT NULL, brand VARCHAR(255) NOT NULL, model VARCHAR(255) NOT NULL, year VARCHAR(4) NOT NULL, engine VARCHAR(255) NOT NULL, preparation VARCHAR(255) NOT NULL, photos VARCHAR(255) DEFAULT NULL, user_id_id INT DEFAULT NULL, INDEX IDX_1B80E4869D86650F (user_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE postphoto ADD CONSTRAINT FK_80C793089D86650F FOREIGN KEY (user_id_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE vehicle ADD CONSTRAINT FK_1B80E4869D86650F FOREIGN KEY (user_id_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE postphoto DROP FOREIGN KEY FK_80C793089D86650F');
        $this->addSql('ALTER TABLE vehicle DROP FOREIGN KEY FK_1B80E4869D86650F');
        $this->addSql('DROP TABLE postphoto');
        $this->addSql('DROP TABLE vehicle');
    }
}
