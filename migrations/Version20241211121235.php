<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241211121235 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE respuesta (id INT AUTO_INCREMENT NOT NULL, usuario_id INT NOT NULL, pregunta_id INT NOT NULL, timestamp DATETIME NOT NULL, respuesta VARCHAR(255) NOT NULL, INDEX IDX_6C6EC5EEDB38439E (usuario_id), INDEX IDX_6C6EC5EE31A5801E (pregunta_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE respuesta ADD CONSTRAINT FK_6C6EC5EEDB38439E FOREIGN KEY (usuario_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE respuesta ADD CONSTRAINT FK_6C6EC5EE31A5801E FOREIGN KEY (pregunta_id) REFERENCES pregunta (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE respuesta DROP FOREIGN KEY FK_6C6EC5EEDB38439E');
        $this->addSql('ALTER TABLE respuesta DROP FOREIGN KEY FK_6C6EC5EE31A5801E');
        $this->addSql('DROP TABLE respuesta');
    }
}
