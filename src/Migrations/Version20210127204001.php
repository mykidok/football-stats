<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210127204001 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE combination_bet (combination_id INT NOT NULL, bet_id INT NOT NULL, INDEX IDX_28B9E0E7D949DCC (combination_id), INDEX IDX_28B9E0ED871DC26 (bet_id), PRIMARY KEY(combination_id, bet_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE combination_bet ADD CONSTRAINT FK_28B9E0E7D949DCC FOREIGN KEY (combination_id) REFERENCES combination (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE combination_bet ADD CONSTRAINT FK_28B9E0ED871DC26 FOREIGN KEY (bet_id) REFERENCES bet (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE bet DROP FOREIGN KEY FK_FBF0EC9B7D949DCC');
        $this->addSql('DROP INDEX IDX_FBF0EC9B7D949DCC ON bet');
        $this->addSql('ALTER TABLE bet DROP combination_id');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE combination_bet');
        $this->addSql('ALTER TABLE bet ADD combination_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE bet ADD CONSTRAINT FK_FBF0EC9B7D949DCC FOREIGN KEY (combination_id) REFERENCES combination (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_FBF0EC9B7D949DCC ON bet (combination_id)');
    }
}
