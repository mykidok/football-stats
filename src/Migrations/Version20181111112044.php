<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181111112044 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE team (id INT AUTO_INCREMENT NOT NULL, championship_id INT DEFAULT NULL, name VARCHAR(60) NOT NULL, nb_goals_per_match_home DOUBLE PRECISION DEFAULT \'0\', nb_goals_per_match_away DOUBLE PRECISION DEFAULT \'0\', api_id INT NOT NULL, UNIQUE INDEX UNIQ_C4E0A61F5E237E06 (name), UNIQUE INDEX UNIQ_C4E0A61F54963938 (api_id), INDEX IDX_C4E0A61F94DDBCE9 (championship_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE game (id INT AUTO_INCREMENT NOT NULL, home_team_id INT NOT NULL, away_team_id INT NOT NULL, championship_id INT DEFAULT NULL, previsional_nb_goals DOUBLE PRECISION DEFAULT NULL, real_nb_goals INT DEFAULT NULL, date DATETIME NOT NULL, good_result TINYINT(1) DEFAULT NULL, api_id INT NOT NULL, UNIQUE INDEX UNIQ_232B318C54963938 (api_id), INDEX IDX_232B318C9C4C13F6 (home_team_id), INDEX IDX_232B318C45185D02 (away_team_id), INDEX IDX_232B318C94DDBCE9 (championship_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE team ADD CONSTRAINT FK_C4E0A61F94DDBCE9 FOREIGN KEY (championship_id) REFERENCES championship (id)');
        $this->addSql('ALTER TABLE game ADD CONSTRAINT FK_232B318C9C4C13F6 FOREIGN KEY (home_team_id) REFERENCES team (id)');
        $this->addSql('ALTER TABLE game ADD CONSTRAINT FK_232B318C45185D02 FOREIGN KEY (away_team_id) REFERENCES team (id)');
        $this->addSql('ALTER TABLE game ADD CONSTRAINT FK_232B318C94DDBCE9 FOREIGN KEY (championship_id) REFERENCES championship (id)');
        $this->addSql('ALTER TABLE championship ADD api_id INT NOT NULL, DROP nb_match, DROP nb_match_succeeded');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE game DROP FOREIGN KEY FK_232B318C9C4C13F6');
        $this->addSql('ALTER TABLE game DROP FOREIGN KEY FK_232B318C45185D02');
        $this->addSql('DROP TABLE team');
        $this->addSql('DROP TABLE game');
        $this->addSql('ALTER TABLE championship ADD nb_match INT DEFAULT 0 NOT NULL, ADD nb_match_succeeded INT DEFAULT 0 NOT NULL, DROP api_id');
    }
}
