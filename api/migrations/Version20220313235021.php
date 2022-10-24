<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220313235021 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE token DROP CONSTRAINT fk_5f37a13bc6582a33');
        $this->addSql('DROP INDEX uniq_5f37a13bc6582a33');
        $this->addSql('ALTER TABLE token DROP user_session_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE token ADD user_session_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE token ADD CONSTRAINT fk_5f37a13bc6582a33 FOREIGN KEY (user_session_id) REFERENCES user_session (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX uniq_5f37a13bc6582a33 ON token (user_session_id)');
    }
}
