<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260416124214 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event_photo DROP FOREIGN KEY `FK_5A8DA7D03E5F2F7B`');
        $this->addSql('ALTER TABLE event_photo ADD CONSTRAINT FK_55AC35343E5F2F7B FOREIGN KEY (event_id_id) REFERENCES events (id)');
        $this->addSql('ALTER TABLE event_photo RENAME INDEX idx_5a8da7d03e5f2f7b TO IDX_55AC35343E5F2F7B');
        $this->addSql('ALTER TABLE event_rating DROP FOREIGN KEY `FK_C49C43E33E5F2F7B`');
        $this->addSql('ALTER TABLE event_rating DROP FOREIGN KEY `FK_C49C43E39D86650F`');
        $this->addSql('DROP INDEX uniq_event_user ON event_rating');
        $this->addSql('ALTER TABLE event_rating ADD CONSTRAINT FK_EA1051703E5F2F7B FOREIGN KEY (event_id_id) REFERENCES events (id)');
        $this->addSql('ALTER TABLE event_rating ADD CONSTRAINT FK_EA1051709D86650F FOREIGN KEY (user_id_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE event_rating RENAME INDEX idx_c49c43e33e5f2f7b TO IDX_EA1051703E5F2F7B');
        $this->addSql('ALTER TABLE event_rating RENAME INDEX idx_c49c43e39d86650f TO IDX_EA1051709D86650F');
        $this->addSql('ALTER TABLE vehicle_photo DROP FOREIGN KEY `FK_5F1E7A1A1B80E486`');
        $this->addSql('ALTER TABLE vehicle_photo ADD CONSTRAINT FK_761804F41DEB1EBB FOREIGN KEY (vehicle_id_id) REFERENCES vehicle (id)');
        $this->addSql('ALTER TABLE vehicle_photo RENAME INDEX idx_5f1e7a1a1b80e486 TO IDX_761804F41DEB1EBB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event_photo DROP FOREIGN KEY FK_55AC35343E5F2F7B');
        $this->addSql('ALTER TABLE event_photo ADD CONSTRAINT `FK_5A8DA7D03E5F2F7B` FOREIGN KEY (event_id_id) REFERENCES events (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE event_photo RENAME INDEX idx_55ac35343e5f2f7b TO IDX_5A8DA7D03E5F2F7B');
        $this->addSql('ALTER TABLE event_rating DROP FOREIGN KEY FK_EA1051703E5F2F7B');
        $this->addSql('ALTER TABLE event_rating DROP FOREIGN KEY FK_EA1051709D86650F');
        $this->addSql('ALTER TABLE event_rating ADD CONSTRAINT `FK_C49C43E33E5F2F7B` FOREIGN KEY (event_id_id) REFERENCES events (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE event_rating ADD CONSTRAINT `FK_C49C43E39D86650F` FOREIGN KEY (user_id_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('CREATE UNIQUE INDEX uniq_event_user ON event_rating (event_id_id, user_id_id)');
        $this->addSql('ALTER TABLE event_rating RENAME INDEX idx_ea1051703e5f2f7b TO IDX_C49C43E33E5F2F7B');
        $this->addSql('ALTER TABLE event_rating RENAME INDEX idx_ea1051709d86650f TO IDX_C49C43E39D86650F');
        $this->addSql('ALTER TABLE vehicle_photo DROP FOREIGN KEY FK_761804F41DEB1EBB');
        $this->addSql('ALTER TABLE vehicle_photo ADD CONSTRAINT `FK_5F1E7A1A1B80E486` FOREIGN KEY (vehicle_id_id) REFERENCES vehicle (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE vehicle_photo RENAME INDEX idx_761804f41deb1ebb TO IDX_5F1E7A1A1B80E486');
    }
}
