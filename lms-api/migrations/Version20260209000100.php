<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260209000100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add daily route tracking summary table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE time_tracking_route_daily (id SERIAL NOT NULL, user_id INT NOT NULL, curso_id INT DEFAULT NULL, day DATE NOT NULL, route VARCHAR(255) NOT NULL, seconds INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_TIME_TRACKING_ROUTE_USER ON time_tracking_route_daily (user_id)');
        $this->addSql('CREATE INDEX IDX_TIME_TRACKING_ROUTE_CURSO ON time_tracking_route_daily (curso_id)');
        $this->addSql('CREATE INDEX IDX_TIME_TRACKING_ROUTE_DAY ON time_tracking_route_daily (day)');
        $this->addSql('CREATE INDEX IDX_TIME_TRACKING_ROUTE_ROUTE ON time_tracking_route_daily (route)');
        $this->addSql('ALTER TABLE time_tracking_route_daily ADD CONSTRAINT FK_TIME_TRACKING_ROUTE_USER FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE time_tracking_route_daily ADD CONSTRAINT FK_TIME_TRACKING_ROUTE_CURSO FOREIGN KEY (curso_id) REFERENCES curso (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE time_tracking_route_daily DROP CONSTRAINT FK_TIME_TRACKING_ROUTE_USER');
        $this->addSql('ALTER TABLE time_tracking_route_daily DROP CONSTRAINT FK_TIME_TRACKING_ROUTE_CURSO');
        $this->addSql('DROP TABLE time_tracking_route_daily');
    }
}
