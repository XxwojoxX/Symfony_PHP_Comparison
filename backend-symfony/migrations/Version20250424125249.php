<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250424125249 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE comment CHANGE updated_at updated_at DATETIME DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE media ADD post_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE media ADD CONSTRAINT FK_6A2CA10C4B89032C FOREIGN KEY (post_id) REFERENCES posts (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_6A2CA10C4B89032C ON media (post_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE posts DROP published_at, CHANGE updated_at updated_at DATETIME DEFAULT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE comment CHANGE updated_at updated_at DATETIME DEFAULT 'NULL'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE media DROP FOREIGN KEY FK_6A2CA10C4B89032C
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_6A2CA10C4B89032C ON media
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE media DROP post_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE posts ADD published_at DATETIME DEFAULT 'NULL', CHANGE updated_at updated_at DATETIME DEFAULT 'NULL'
        SQL);
    }
}
