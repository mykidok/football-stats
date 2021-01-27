<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210123163736 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE bet (id INT AUTO_INCREMENT NOT NULL, game_id INT DEFAULT NULL, combination_id INT DEFAULT NULL, winner_id INT DEFAULT NULL, odd DOUBLE PRECISION DEFAULT NULL, good_result TINYINT(1) DEFAULT NULL, form TINYINT(1) DEFAULT NULL, percentage DOUBLE PRECISION DEFAULT NULL, my_odd DOUBLE PRECISION DEFAULT NULL, type VARCHAR(255) NOT NULL, discr VARCHAR(255) NOT NULL, win_or_draw TINYINT(1) DEFAULT NULL, prevision_is_same_as_expected TINYINT(1) DEFAULT NULL, INDEX IDX_FBF0EC9BE48FD905 (game_id), INDEX IDX_FBF0EC9B7D949DCC (combination_id), INDEX IDX_FBF0EC9B5DFCD4B8 (winner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE country (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(30) NOT NULL, flag_path VARCHAR(150) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE bet ADD CONSTRAINT FK_FBF0EC9BE48FD905 FOREIGN KEY (game_id) REFERENCES game (id)');
        $this->addSql('ALTER TABLE bet ADD CONSTRAINT FK_FBF0EC9B7D949DCC FOREIGN KEY (combination_id) REFERENCES combination (id)');
        $this->addSql('ALTER TABLE bet ADD CONSTRAINT FK_FBF0EC9B5DFCD4B8 FOREIGN KEY (winner_id) REFERENCES team (id)');
        $this->addSql('ALTER TABLE championship ADD country_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE championship ADD CONSTRAINT FK_EBADDE6AF92F3E70 FOREIGN KEY (country_id) REFERENCES country (id)');
        $this->addSql('CREATE INDEX IDX_EBADDE6AF92F3E70 ON championship (country_id)');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE championship DROP FOREIGN KEY FK_EBADDE6AF92F3E70');
        $this->addSql('DROP TABLE bet');
        $this->addSql('DROP TABLE country');
        $this->addSql('DROP INDEX IDX_EBADDE6AF92F3E70 ON championship');
        $this->addSql('ALTER TABLE championship DROP country_id');
    }
}
