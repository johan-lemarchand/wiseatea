<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220313234737 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE user_session_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE user_session (id INT NOT NULL, token_id INT NOT NULL, user_id INT NOT NULL, lasted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, finished_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT \'1000-01-01 00:00:00\' NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT \'1000-01-01 00:00:00\' NOT NULL, user_ip VARCHAR(45) NOT NULL, user_agent VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8849CBDE41DEE7B9 ON user_session (token_id)');
        $this->addSql('CREATE INDEX IDX_8849CBDEA76ED395 ON user_session (user_id)');
        $this->addSql('ALTER TABLE user_session ADD CONSTRAINT FK_8849CBDE41DEE7B9 FOREIGN KEY (token_id) REFERENCES token (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_session ADD CONSTRAINT FK_8849CBDEA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE token ADD user_session_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE token ADD CONSTRAINT FK_5F37A13BC6582A33 FOREIGN KEY (user_session_id) REFERENCES user_session (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5F37A13BC6582A33 ON token (user_session_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE token DROP CONSTRAINT FK_5F37A13BC6582A33');
        $this->addSql('DROP SEQUENCE user_session_id_seq CASCADE');
        $this->addSql('DROP TABLE user_session');
        $this->addSql('DROP INDEX UNIQ_5F37A13BC6582A33');
        $this->addSql('ALTER TABLE token DROP user_session_id');
    }
}
