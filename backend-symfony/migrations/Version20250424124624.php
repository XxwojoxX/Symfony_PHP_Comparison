<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250424124624 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE posts_media (post_id INT NOT NULL, media_id INT NOT NULL, INDEX IDX_D82BFA1D4B89032C (post_id), INDEX IDX_D82BFA1DEA9FDD75 (media_id), PRIMARY KEY(post_id, media_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE posts_media ADD CONSTRAINT FK_D82BFA1D4B89032C FOREIGN KEY (post_id) REFERENCES posts (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE posts_media ADD CONSTRAINT FK_D82BFA1DEA9FDD75 FOREIGN KEY (media_id) REFERENCES media (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE comment CHANGE updated_at updated_at DATETIME DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE media DROP FOREIGN KEY FK_6A2CA10C4B89032C
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_6A2CA10C4B89032C ON media
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE media ADD file_name VARCHAR(255) NOT NULL, ADD updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', DROP post_id, DROP file_type, DROP title, DROP description
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE posts CHANGE updated_at updated_at DATETIME DEFAULT NULL, CHANGE published_at published_at DATETIME DEFAULT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE posts_media DROP FOREIGN KEY FK_D82BFA1D4B89032C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE posts_media DROP FOREIGN KEY FK_D82BFA1DEA9FDD75
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE posts_media
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE comment CHANGE updated_at updated_at DATETIME DEFAULT 'NULL'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE media ADD post_id INT NOT NULL, ADD title VARCHAR(255) NOT NULL, ADD description LONGTEXT NOT NULL, DROP updated_at, CHANGE file_name file_type VARCHAR(255) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE media ADD CONSTRAINT FK_6A2CA10C4B89032C FOREIGN KEY (post_id) REFERENCES posts (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_6A2CA10C4B89032C ON media (post_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE posts CHANGE updated_at updated_at DATETIME DEFAULT 'NULL', CHANGE published_at published_at DATETIME DEFAULT 'NULL'
        SQL);
    }
}
