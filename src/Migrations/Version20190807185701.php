<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190807185701 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE championship_historic (id INT AUTO_INCREMENT NOT NULL, championship_id INT DEFAULT NULL, season INT NOT NULL, average_goals_home_for DOUBLE PRECISION NOT NULL, average_goals_home_against DOUBLE PRECISION NOT NULL, average_goals_away_for DOUBLE PRECISION NOT NULL, average_goals_away_against DOUBLE PRECISION NOT NULL, INDEX IDX_3B79CCD94DDBCE9 (championship_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE team_historic (id INT AUTO_INCREMENT NOT NULL, team_id INT DEFAULT NULL, championship_historic_id INT DEFAULT NULL, season INT NOT NULL, home_force_attack DOUBLE PRECISION NOT NULL, home_force_defense DOUBLE PRECISION NOT NULL, away_force_attack DOUBLE PRECISION NOT NULL, away_force_defense DOUBLE PRECISION NOT NULL, INDEX IDX_15103328296CD8AE (team_id), INDEX IDX_1510332877C72319 (championship_historic_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE championship_historic ADD CONSTRAINT FK_3B79CCD94DDBCE9 FOREIGN KEY (championship_id) REFERENCES championship (id)');
        $this->addSql('ALTER TABLE team_historic ADD CONSTRAINT FK_15103328296CD8AE FOREIGN KEY (team_id) REFERENCES team (id)');
        $this->addSql('ALTER TABLE team_historic ADD CONSTRAINT FK_1510332877C72319 FOREIGN KEY (championship_historic_id) REFERENCES championship_historic (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE team_historic DROP FOREIGN KEY FK_1510332877C72319');
        $this->addSql('DROP TABLE championship_historic');
        $this->addSql('DROP TABLE team_historic');
    }
}
