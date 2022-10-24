<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220318170851 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE "user" ADD cgu BOOLEAN NOT NULL');
        $this->addSql('ALTER TABLE "user" ADD share_data BOOLEAN NOT NULL');
        $this->addSql('ALTER TABLE "user" ALTER gender DROP NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE "user" DROP cgu');
        $this->addSql('ALTER TABLE "user" DROP share_data');
        $this->addSql('ALTER TABLE "user" ALTER gender SET NOT NULL');
    }
}
