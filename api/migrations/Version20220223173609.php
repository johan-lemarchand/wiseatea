<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220223173609 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE "user_id_seq" INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE "user" (id INT NOT NULL, users_create_id INT DEFAULT NULL, users_update_id INT DEFAULT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, num_internal_user VARCHAR(20) DEFAULT NULL, gender INT NOT NULL, firstname VARCHAR(40) NOT NULL, lastname VARCHAR(40) NOT NULL, email2 VARCHAR(255) DEFAULT NULL, birthday_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, avatar TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, token TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON "user" (email)');
        $this->addSql('CREATE INDEX IDX_8D93D6498863A762 ON "user" (users_create_id)');
        $this->addSql('CREATE INDEX IDX_8D93D649B3EB9050 ON "user" (users_update_id)');
        $this->addSql('ALTER TABLE "user" ADD CONSTRAINT FK_8D93D6498863A762 FOREIGN KEY (users_create_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "user" ADD CONSTRAINT FK_8D93D649B3EB9050 FOREIGN KEY (users_update_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE "user" DROP CONSTRAINT FK_8D93D6498863A762');
        $this->addSql('ALTER TABLE "user" DROP CONSTRAINT FK_8D93D649B3EB9050');
        $this->addSql('DROP SEQUENCE "user_id_seq" CASCADE');
        $this->addSql('DROP TABLE "user"');
    }
}
