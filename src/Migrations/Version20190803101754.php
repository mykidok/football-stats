<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190803101754 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE game ADD expected_nb_goals INT DEFAULT NULL, ADD prevision_is_same_as_expected TINYINT(1) DEFAULT NULL, ADD average_expected_nb_goals DOUBLE PRECISION DEFAULT NULL, ADD my_odd DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE team ADD home_force_attack DOUBLE PRECISION DEFAULT NULL, ADD home_force_defense DOUBLE PRECISION DEFAULT NULL, ADD away_force_attack DOUBLE PRECISION DEFAULT NULL, ADD away_force_defense DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE championship ADD average_goals_home_for DOUBLE PRECISION DEFAULT NULL, ADD average_goals_home_against DOUBLE PRECISION DEFAULT NULL, ADD average_goals_away_for DOUBLE PRECISION DEFAULT NULL, ADD average_goals_away_against DOUBLE PRECISION DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE championship DROP average_goals_home_for, DROP average_goals_home_against, DROP average_goals_away_for, DROP average_goals_away_against');
        $this->addSql('ALTER TABLE game DROP expected_nb_goals, DROP prevision_is_same_as_expected, DROP average_expected_nb_goals, DROP my_odd');
        $this->addSql('ALTER TABLE team DROP home_force_attack, DROP home_force_defense, DROP away_force_attack, DROP away_force_defense');
    }
}
