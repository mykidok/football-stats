<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210201185359 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE country CHANGE name name VARCHAR(60) NOT NULL');
        $this->addSql('ALTER TABLE game DROP FOREIGN KEY FK_232B318C1AE9FF7F');
        $this->addSql('DROP INDEX IDX_232B318C1AE9FF7F ON game');
        $this->addSql('ALTER TABLE game ADD finished TINYINT(1) DEFAULT NULL, DROP previsional_winner_id, DROP moment_form, DROP odd, DROP percentage, DROP prevision_is_same_as_expected, DROP my_odd, DROP winner_result, DROP winner_odd, DROP winner_moment_form, DROP winner_percentage, DROP bet_on_winner');
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE country CHANGE name name VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE game ADD previsional_winner_id INT DEFAULT NULL, ADD odd DOUBLE PRECISION DEFAULT NULL, ADD percentage DOUBLE PRECISION DEFAULT NULL, ADD prevision_is_same_as_expected TINYINT(1) DEFAULT NULL, ADD my_odd DOUBLE PRECISION DEFAULT NULL, ADD winner_result TINYINT(1) DEFAULT NULL, ADD winner_odd DOUBLE PRECISION DEFAULT NULL, ADD winner_moment_form TINYINT(1) DEFAULT NULL, ADD winner_percentage DOUBLE PRECISION DEFAULT NULL, ADD bet_on_winner TINYINT(1) NOT NULL, CHANGE finished moment_form TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE game ADD CONSTRAINT FK_232B318C1AE9FF7F FOREIGN KEY (previsional_winner_id) REFERENCES team (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_232B318C1AE9FF7F ON game (previsional_winner_id)');
    }
}
