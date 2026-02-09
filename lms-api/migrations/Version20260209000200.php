<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260209000200 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add actividad entregas (submissions) table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE actividad_entrega (id SERIAL NOT NULL, actividad_id INT NOT NULL, user_id INT NOT NULL, file_id INT DEFAULT NULL, content TEXT DEFAULT NULL, status VARCHAR(20) NOT NULL, grade DOUBLE PRECISION DEFAULT NULL, feedback TEXT DEFAULT NULL, submitted_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, graded_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_ACTIVIDAD_ENTREGA_ACTIVIDAD ON actividad_entrega (actividad_id)');
        $this->addSql('CREATE INDEX IDX_ACTIVIDAD_ENTREGA_USER ON actividad_entrega (user_id)');
        $this->addSql('CREATE INDEX IDX_ACTIVIDAD_ENTREGA_STATUS ON actividad_entrega (status)');
        $this->addSql('ALTER TABLE actividad_entrega ADD CONSTRAINT FK_ACTIVIDAD_ENTREGA_ACTIVIDAD FOREIGN KEY (actividad_id) REFERENCES actividad (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE actividad_entrega ADD CONSTRAINT FK_ACTIVIDAD_ENTREGA_USER FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE actividad_entrega ADD CONSTRAINT FK_ACTIVIDAD_ENTREGA_FILE FOREIGN KEY (file_id) REFERENCES file_object (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE actividad_entrega DROP CONSTRAINT FK_ACTIVIDAD_ENTREGA_ACTIVIDAD');
        $this->addSql('ALTER TABLE actividad_entrega DROP CONSTRAINT FK_ACTIVIDAD_ENTREGA_USER');
        $this->addSql('ALTER TABLE actividad_entrega DROP CONSTRAINT FK_ACTIVIDAD_ENTREGA_FILE');
        $this->addSql('DROP TABLE actividad_entrega');
    }
}
