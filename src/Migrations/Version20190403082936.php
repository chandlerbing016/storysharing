<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190403082936 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user CHANGE username pubusername VARCHAR(225) NOT NULL');
        $this->addSql('ALTER TABLE story CHANGE view_counter view_counter INT NOT NULL, CHANGE upvote_counter upvote_counter INT NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE story CHANGE view_counter view_counter INT DEFAULT 0 NOT NULL, CHANGE upvote_counter upvote_counter INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE pubusername username VARCHAR(225) NOT NULL COLLATE utf8mb4_unicode_ci');
    }
}
