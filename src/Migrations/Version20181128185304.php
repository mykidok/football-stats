<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181128185304 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE combination (id INT AUTO_INCREMENT NOT NULL, date DATETIME NOT NULL, success TINYINT(1) DEFAULT NULL, general_odd DOUBLE PRECISION DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE combination_game (combination_id INT NOT NULL, game_id INT NOT NULL, INDEX IDX_A3B72D357D949DCC (combination_id), INDEX IDX_A3B72D35E48FD905 (game_id), PRIMARY KEY(combination_id, game_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE combination_game ADD CONSTRAINT FK_A3B72D357D949DCC FOREIGN KEY (combination_id) REFERENCES combination (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE combination_game ADD CONSTRAINT FK_A3B72D35E48FD905 FOREIGN KEY (game_id) REFERENCES game (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE combination_game DROP FOREIGN KEY FK_A3B72D357D949DCC');
        $this->addSql('DROP TABLE combination');
        $this->addSql('DROP TABLE combination_game');
    }
}
