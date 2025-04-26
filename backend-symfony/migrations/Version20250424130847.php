<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250424130847 extends AbstractMigration
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
            ALTER TABLE media ADD title VARCHAR(255) NOT NULL, ADD description LONGTEXT NOT NULL, DROP updated_at, CHANGE file_name file_type VARCHAR(255) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE posts ADD published_at DATETIME DEFAULT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE comment CHANGE updated_at updated_at DATETIME DEFAULT 'NULL'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE media ADD file_name VARCHAR(255) NOT NULL, ADD updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', DROP file_type, DROP title, DROP description
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE posts DROP published_at, CHANGE updated_at updated_at DATETIME DEFAULT 'NULL'
        SQL);
    }
}
