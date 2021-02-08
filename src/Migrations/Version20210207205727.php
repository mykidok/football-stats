<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210207205727 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE team ADD both_teams_score_form TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE bet ADD both_teams_score TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE game ADD home_team_goals INT DEFAULT NULL, ADD away_team_goals INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE bet DROP both_teams_score');
        $this->addSql('ALTER TABLE game DROP home_team_goals, DROP away_team_goals');
        $this->addSql('ALTER TABLE team DROP both_teams_score_form');
    }
}
