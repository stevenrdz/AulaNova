<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260204000100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initial LMS schema';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE roles (id SERIAL NOT NULL, name VARCHAR(50) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_ROLES_NAME ON roles (name)');

        $this->addSql('CREATE TABLE users (id SERIAL NOT NULL, email VARCHAR(180) NOT NULL, password VARCHAR(255) NOT NULL, first_name VARCHAR(100) NOT NULL, last_name VARCHAR(100) NOT NULL, is_active BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_USERS_EMAIL ON users (email)');

        $this->addSql('CREATE TABLE user_roles (user_id INT NOT NULL, role_id INT NOT NULL, PRIMARY KEY(user_id, role_id))');
        $this->addSql('CREATE INDEX IDX_USER_ROLES_USER ON user_roles (user_id)');
        $this->addSql('CREATE INDEX IDX_USER_ROLES_ROLE ON user_roles (role_id)');
        $this->addSql('ALTER TABLE user_roles ADD CONSTRAINT FK_USER_ROLES_USER FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_roles ADD CONSTRAINT FK_USER_ROLES_ROLE FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE CASCADE');

        $this->addSql('CREATE TABLE refresh_tokens (id SERIAL NOT NULL, user_id INT NOT NULL, token_hash VARCHAR(128) NOT NULL, expires_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, revoked_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, replaced_by VARCHAR(128) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_REFRESH_TOKENS_HASH ON refresh_tokens (token_hash)');
        $this->addSql('CREATE INDEX IDX_REFRESH_TOKENS_USER ON refresh_tokens (user_id)');
        $this->addSql('ALTER TABLE refresh_tokens ADD CONSTRAINT FK_REFRESH_TOKENS_USER FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');

        $this->addSql('CREATE TABLE password_reset_tokens (id SERIAL NOT NULL, user_id INT NOT NULL, otp_hash VARCHAR(128) NOT NULL, expires_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, used_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_PWD_RESET_USER ON password_reset_tokens (user_id)');
        $this->addSql('ALTER TABLE password_reset_tokens ADD CONSTRAINT FK_PWD_RESET_USER FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');

        $this->addSql('CREATE TABLE institution_settings (id SERIAL NOT NULL, logo_url VARCHAR(255) DEFAULT NULL, primary_color VARCHAR(20) DEFAULT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');

        $this->addSql('CREATE TABLE file_object (id SERIAL NOT NULL, created_by_id INT DEFAULT NULL, object_key VARCHAR(255) NOT NULL, bucket VARCHAR(255) NOT NULL, original_name VARCHAR(255) NOT NULL, mime_type VARCHAR(100) NOT NULL, size INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FILE_OBJECT_KEY ON file_object (object_key)');
        $this->addSql('CREATE INDEX IDX_FILE_OBJECT_CREATED_BY ON file_object (created_by_id)');
        $this->addSql('ALTER TABLE file_object ADD CONSTRAINT FK_FILE_OBJECT_CREATED_BY FOREIGN KEY (created_by_id) REFERENCES users (id)');

        $this->addSql('CREATE TABLE audit_log (id SERIAL NOT NULL, user_id INT DEFAULT NULL, action VARCHAR(100) NOT NULL, entity_type VARCHAR(100) NOT NULL, entity_id VARCHAR(50) DEFAULT NULL, ip_address VARCHAR(45) DEFAULT NULL, user_agent VARCHAR(255) DEFAULT NULL, metadata JSON DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_AUDIT_LOG_USER ON audit_log (user_id)');
        $this->addSql('ALTER TABLE audit_log ADD CONSTRAINT FK_AUDIT_LOG_USER FOREIGN KEY (user_id) REFERENCES users (id)');

        $this->addSql('CREATE TABLE students_profile (id SERIAL NOT NULL, user_id INT NOT NULL, document_number VARCHAR(50) DEFAULT NULL, phone VARCHAR(30) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_STUDENT_PROFILE_USER ON students_profile (user_id)');
        $this->addSql('ALTER TABLE students_profile ADD CONSTRAINT FK_STUDENT_PROFILE_USER FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');

        $this->addSql('CREATE TABLE teachers_profile (id SERIAL NOT NULL, user_id INT NOT NULL, document_number VARCHAR(50) DEFAULT NULL, phone VARCHAR(30) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_TEACHER_PROFILE_USER ON teachers_profile (user_id)');
        $this->addSql('ALTER TABLE teachers_profile ADD CONSTRAINT FK_TEACHER_PROFILE_USER FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');

        $this->addSql('CREATE TABLE sede_jornada (id SERIAL NOT NULL, name VARCHAR(120) NOT NULL, is_active BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE nivel (id SERIAL NOT NULL, name VARCHAR(120) NOT NULL, is_active BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE periodo (id SERIAL NOT NULL, name VARCHAR(120) NOT NULL, start_date DATE DEFAULT NULL, end_date DATE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE carrera (id SERIAL NOT NULL, name VARCHAR(120) NOT NULL, is_active BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE asignatura (id SERIAL NOT NULL, name VARCHAR(120) NOT NULL, is_active BOOLEAN NOT NULL, PRIMARY KEY(id))');

        $this->addSql('CREATE TABLE curso (id SERIAL NOT NULL, periodo_id INT DEFAULT NULL, teacher_id INT DEFAULT NULL, sede_jornada_id INT DEFAULT NULL, carrera_id INT DEFAULT NULL, asignatura_id INT DEFAULT NULL, name VARCHAR(150) NOT NULL, capacity INT DEFAULT NULL, start_date DATE DEFAULT NULL, end_date DATE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_CURSO_PERIODO ON curso (periodo_id)');
        $this->addSql('CREATE INDEX IDX_CURSO_TEACHER ON curso (teacher_id)');
        $this->addSql('CREATE INDEX IDX_CURSO_SEDE ON curso (sede_jornada_id)');
        $this->addSql('CREATE INDEX IDX_CURSO_CARRERA ON curso (carrera_id)');
        $this->addSql('CREATE INDEX IDX_CURSO_ASIGNATURA ON curso (asignatura_id)');
        $this->addSql('ALTER TABLE curso ADD CONSTRAINT FK_CURSO_PERIODO FOREIGN KEY (periodo_id) REFERENCES periodo (id)');
        $this->addSql('ALTER TABLE curso ADD CONSTRAINT FK_CURSO_TEACHER FOREIGN KEY (teacher_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE curso ADD CONSTRAINT FK_CURSO_SEDE FOREIGN KEY (sede_jornada_id) REFERENCES sede_jornada (id)');
        $this->addSql('ALTER TABLE curso ADD CONSTRAINT FK_CURSO_CARRERA FOREIGN KEY (carrera_id) REFERENCES carrera (id)');
        $this->addSql('ALTER TABLE curso ADD CONSTRAINT FK_CURSO_ASIGNATURA FOREIGN KEY (asignatura_id) REFERENCES asignatura (id)');

        $this->addSql('CREATE TABLE curso_users (id SERIAL NOT NULL, curso_id INT NOT NULL, user_id INT NOT NULL, role VARCHAR(30) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_CURSO_USERS_CURSO ON curso_users (curso_id)');
        $this->addSql('CREATE INDEX IDX_CURSO_USERS_USER ON curso_users (user_id)');
        $this->addSql('ALTER TABLE curso_users ADD CONSTRAINT FK_CURSO_USERS_CURSO FOREIGN KEY (curso_id) REFERENCES curso (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE curso_users ADD CONSTRAINT FK_CURSO_USERS_USER FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');

        $this->addSql('CREATE TABLE curso_virtual (id SERIAL NOT NULL, curso_id INT NOT NULL, description TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_CURSO_VIRTUAL_CURSO ON curso_virtual (curso_id)');
        $this->addSql('ALTER TABLE curso_virtual ADD CONSTRAINT FK_CURSO_VIRTUAL_CURSO FOREIGN KEY (curso_id) REFERENCES curso (id) ON DELETE CASCADE');

        $this->addSql('CREATE TABLE anuncio (id SERIAL NOT NULL, curso_virtual_id INT NOT NULL, created_by_id INT DEFAULT NULL, title VARCHAR(200) NOT NULL, content TEXT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_ANUNCIO_CURSO_VIRTUAL ON anuncio (curso_virtual_id)');
        $this->addSql('CREATE INDEX IDX_ANUNCIO_CREATED_BY ON anuncio (created_by_id)');
        $this->addSql('ALTER TABLE anuncio ADD CONSTRAINT FK_ANUNCIO_CURSO_VIRTUAL FOREIGN KEY (curso_virtual_id) REFERENCES curso_virtual (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE anuncio ADD CONSTRAINT FK_ANUNCIO_CREATED_BY FOREIGN KEY (created_by_id) REFERENCES users (id)');

        $this->addSql('CREATE TABLE actividad (id SERIAL NOT NULL, curso_virtual_id INT NOT NULL, file_id INT DEFAULT NULL, type VARCHAR(20) NOT NULL, title VARCHAR(200) NOT NULL, content TEXT DEFAULT NULL, youtube_url VARCHAR(255) DEFAULT NULL, is_graded BOOLEAN NOT NULL, due_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_ACTIVIDAD_CURSO_VIRTUAL ON actividad (curso_virtual_id)');
        $this->addSql('CREATE INDEX IDX_ACTIVIDAD_FILE ON actividad (file_id)');
        $this->addSql('ALTER TABLE actividad ADD CONSTRAINT FK_ACTIVIDAD_CURSO_VIRTUAL FOREIGN KEY (curso_virtual_id) REFERENCES curso_virtual (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE actividad ADD CONSTRAINT FK_ACTIVIDAD_FILE FOREIGN KEY (file_id) REFERENCES file_object (id)');

        $this->addSql('CREATE TABLE actividad_attachment (id SERIAL NOT NULL, actividad_id INT NOT NULL, file_id INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_ACTIVIDAD_ATTACHMENT_ACTIVIDAD ON actividad_attachment (actividad_id)');
        $this->addSql('CREATE INDEX IDX_ACTIVIDAD_ATTACHMENT_FILE ON actividad_attachment (file_id)');
        $this->addSql('ALTER TABLE actividad_attachment ADD CONSTRAINT FK_ACTIVIDAD_ATTACHMENT_ACTIVIDAD FOREIGN KEY (actividad_id) REFERENCES actividad (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE actividad_attachment ADD CONSTRAINT FK_ACTIVIDAD_ATTACHMENT_FILE FOREIGN KEY (file_id) REFERENCES file_object (id)');

        $this->addSql('CREATE TABLE quiz (id SERIAL NOT NULL, curso_virtual_id INT NOT NULL, title VARCHAR(200) NOT NULL, description TEXT DEFAULT NULL, start_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, end_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, time_limit_minutes INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_QUIZ_CURSO_VIRTUAL ON quiz (curso_virtual_id)');
        $this->addSql('ALTER TABLE quiz ADD CONSTRAINT FK_QUIZ_CURSO_VIRTUAL FOREIGN KEY (curso_virtual_id) REFERENCES curso_virtual (id) ON DELETE CASCADE');

        $this->addSql('CREATE TABLE question (id SERIAL NOT NULL, quiz_id INT NOT NULL, type VARCHAR(30) NOT NULL, prompt TEXT NOT NULL, options JSON DEFAULT NULL, correct_option VARCHAR(20) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_QUESTION_QUIZ ON question (quiz_id)');
        $this->addSql('ALTER TABLE question ADD CONSTRAINT FK_QUESTION_QUIZ FOREIGN KEY (quiz_id) REFERENCES quiz (id) ON DELETE CASCADE');

        $this->addSql('CREATE TABLE attempt (id SERIAL NOT NULL, quiz_id INT NOT NULL, user_id INT NOT NULL, started_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, finished_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, score DOUBLE PRECISION DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_ATTEMPT_QUIZ ON attempt (quiz_id)');
        $this->addSql('CREATE INDEX IDX_ATTEMPT_USER ON attempt (user_id)');
        $this->addSql('ALTER TABLE attempt ADD CONSTRAINT FK_ATTEMPT_QUIZ FOREIGN KEY (quiz_id) REFERENCES quiz (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE attempt ADD CONSTRAINT FK_ATTEMPT_USER FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');

        $this->addSql('CREATE TABLE answer (id SERIAL NOT NULL, attempt_id INT NOT NULL, question_id INT NOT NULL, answer_text TEXT DEFAULT NULL, is_correct BOOLEAN DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_ANSWER_ATTEMPT ON answer (attempt_id)');
        $this->addSql('CREATE INDEX IDX_ANSWER_QUESTION ON answer (question_id)');
        $this->addSql('ALTER TABLE answer ADD CONSTRAINT FK_ANSWER_ATTEMPT FOREIGN KEY (attempt_id) REFERENCES attempt (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE answer ADD CONSTRAINT FK_ANSWER_QUESTION FOREIGN KEY (question_id) REFERENCES question (id) ON DELETE CASCADE');

        $this->addSql('CREATE TABLE import_batch (id SERIAL NOT NULL, created_by_id INT DEFAULT NULL, result_file_id INT DEFAULT NULL, type VARCHAR(50) NOT NULL, status VARCHAR(30) NOT NULL, total_rows INT DEFAULT NULL, success_count INT DEFAULT NULL, error_count INT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_IMPORT_BATCH_CREATED_BY ON import_batch (created_by_id)');
        $this->addSql('CREATE INDEX IDX_IMPORT_BATCH_RESULT_FILE ON import_batch (result_file_id)');
        $this->addSql('ALTER TABLE import_batch ADD CONSTRAINT FK_IMPORT_BATCH_CREATED_BY FOREIGN KEY (created_by_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE import_batch ADD CONSTRAINT FK_IMPORT_BATCH_RESULT_FILE FOREIGN KEY (result_file_id) REFERENCES file_object (id)');

        $this->addSql('CREATE TABLE import_row_error (id SERIAL NOT NULL, batch_id INT NOT NULL, row_number INT NOT NULL, message TEXT NOT NULL, raw_data JSON DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_IMPORT_ROW_ERROR_BATCH ON import_row_error (batch_id)');
        $this->addSql('ALTER TABLE import_row_error ADD CONSTRAINT FK_IMPORT_ROW_ERROR_BATCH FOREIGN KEY (batch_id) REFERENCES import_batch (id) ON DELETE CASCADE');

        $this->addSql('CREATE TABLE import_files (id SERIAL NOT NULL, batch_id INT NOT NULL, file_id INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_IMPORT_FILES_BATCH ON import_files (batch_id)');
        $this->addSql('CREATE INDEX IDX_IMPORT_FILES_FILE ON import_files (file_id)');
        $this->addSql('ALTER TABLE import_files ADD CONSTRAINT FK_IMPORT_FILES_BATCH FOREIGN KEY (batch_id) REFERENCES import_batch (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE import_files ADD CONSTRAINT FK_IMPORT_FILES_FILE FOREIGN KEY (file_id) REFERENCES file_object (id) ON DELETE CASCADE');

        $this->addSql('CREATE TABLE time_tracking_daily (id SERIAL NOT NULL, user_id INT NOT NULL, curso_id INT DEFAULT NULL, day DATE NOT NULL, seconds INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_TIME_TRACKING_USER ON time_tracking_daily (user_id)');
        $this->addSql('CREATE INDEX IDX_TIME_TRACKING_CURSO ON time_tracking_daily (curso_id)');
        $this->addSql('ALTER TABLE time_tracking_daily ADD CONSTRAINT FK_TIME_TRACKING_USER FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE time_tracking_daily ADD CONSTRAINT FK_TIME_TRACKING_CURSO FOREIGN KEY (curso_id) REFERENCES curso (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE time_tracking_daily DROP CONSTRAINT FK_TIME_TRACKING_USER');
        $this->addSql('ALTER TABLE time_tracking_daily DROP CONSTRAINT FK_TIME_TRACKING_CURSO');
        $this->addSql('ALTER TABLE import_files DROP CONSTRAINT FK_IMPORT_FILES_BATCH');
        $this->addSql('ALTER TABLE import_files DROP CONSTRAINT FK_IMPORT_FILES_FILE');
        $this->addSql('ALTER TABLE import_row_error DROP CONSTRAINT FK_IMPORT_ROW_ERROR_BATCH');
        $this->addSql('ALTER TABLE import_batch DROP CONSTRAINT FK_IMPORT_BATCH_CREATED_BY');
        $this->addSql('ALTER TABLE import_batch DROP CONSTRAINT FK_IMPORT_BATCH_RESULT_FILE');
        $this->addSql('ALTER TABLE answer DROP CONSTRAINT FK_ANSWER_ATTEMPT');
        $this->addSql('ALTER TABLE answer DROP CONSTRAINT FK_ANSWER_QUESTION');
        $this->addSql('ALTER TABLE attempt DROP CONSTRAINT FK_ATTEMPT_QUIZ');
        $this->addSql('ALTER TABLE attempt DROP CONSTRAINT FK_ATTEMPT_USER');
        $this->addSql('ALTER TABLE question DROP CONSTRAINT FK_QUESTION_QUIZ');
        $this->addSql('ALTER TABLE quiz DROP CONSTRAINT FK_QUIZ_CURSO_VIRTUAL');
        $this->addSql('ALTER TABLE actividad_attachment DROP CONSTRAINT FK_ACTIVIDAD_ATTACHMENT_ACTIVIDAD');
        $this->addSql('ALTER TABLE actividad_attachment DROP CONSTRAINT FK_ACTIVIDAD_ATTACHMENT_FILE');
        $this->addSql('ALTER TABLE actividad DROP CONSTRAINT FK_ACTIVIDAD_CURSO_VIRTUAL');
        $this->addSql('ALTER TABLE actividad DROP CONSTRAINT FK_ACTIVIDAD_FILE');
        $this->addSql('ALTER TABLE anuncio DROP CONSTRAINT FK_ANUNCIO_CURSO_VIRTUAL');
        $this->addSql('ALTER TABLE anuncio DROP CONSTRAINT FK_ANUNCIO_CREATED_BY');
        $this->addSql('ALTER TABLE curso_virtual DROP CONSTRAINT FK_CURSO_VIRTUAL_CURSO');
        $this->addSql('ALTER TABLE curso_users DROP CONSTRAINT FK_CURSO_USERS_CURSO');
        $this->addSql('ALTER TABLE curso_users DROP CONSTRAINT FK_CURSO_USERS_USER');
        $this->addSql('ALTER TABLE curso DROP CONSTRAINT FK_CURSO_PERIODO');
        $this->addSql('ALTER TABLE curso DROP CONSTRAINT FK_CURSO_TEACHER');
        $this->addSql('ALTER TABLE curso DROP CONSTRAINT FK_CURSO_SEDE');
        $this->addSql('ALTER TABLE curso DROP CONSTRAINT FK_CURSO_CARRERA');
        $this->addSql('ALTER TABLE curso DROP CONSTRAINT FK_CURSO_ASIGNATURA');
        $this->addSql('ALTER TABLE teachers_profile DROP CONSTRAINT FK_TEACHER_PROFILE_USER');
        $this->addSql('ALTER TABLE students_profile DROP CONSTRAINT FK_STUDENT_PROFILE_USER');
        $this->addSql('ALTER TABLE audit_log DROP CONSTRAINT FK_AUDIT_LOG_USER');
        $this->addSql('ALTER TABLE file_object DROP CONSTRAINT FK_FILE_OBJECT_CREATED_BY');
        $this->addSql('ALTER TABLE password_reset_tokens DROP CONSTRAINT FK_PWD_RESET_USER');
        $this->addSql('ALTER TABLE refresh_tokens DROP CONSTRAINT FK_REFRESH_TOKENS_USER');
        $this->addSql('ALTER TABLE user_roles DROP CONSTRAINT FK_USER_ROLES_USER');
        $this->addSql('ALTER TABLE user_roles DROP CONSTRAINT FK_USER_ROLES_ROLE');

        $this->addSql('DROP TABLE time_tracking_daily');
        $this->addSql('DROP TABLE import_files');
        $this->addSql('DROP TABLE import_row_error');
        $this->addSql('DROP TABLE import_batch');
        $this->addSql('DROP TABLE answer');
        $this->addSql('DROP TABLE attempt');
        $this->addSql('DROP TABLE question');
        $this->addSql('DROP TABLE quiz');
        $this->addSql('DROP TABLE actividad_attachment');
        $this->addSql('DROP TABLE actividad');
        $this->addSql('DROP TABLE anuncio');
        $this->addSql('DROP TABLE curso_virtual');
        $this->addSql('DROP TABLE curso_users');
        $this->addSql('DROP TABLE curso');
        $this->addSql('DROP TABLE asignatura');
        $this->addSql('DROP TABLE carrera');
        $this->addSql('DROP TABLE periodo');
        $this->addSql('DROP TABLE nivel');
        $this->addSql('DROP TABLE sede_jornada');
        $this->addSql('DROP TABLE teachers_profile');
        $this->addSql('DROP TABLE students_profile');
        $this->addSql('DROP TABLE audit_log');
        $this->addSql('DROP TABLE file_object');
        $this->addSql('DROP TABLE institution_settings');
        $this->addSql('DROP TABLE password_reset_tokens');
        $this->addSql('DROP TABLE refresh_tokens');
        $this->addSql('DROP TABLE user_roles');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE roles');
    }
}
