<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240930072340 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE documents (id INT AUTO_INCREMENT NOT NULL, instructor_id INT NOT NULL, formation_id INT NOT NULL, title VARCHAR(255) NOT NULL, uploaded_at DATETIME NOT NULL, INDEX IDX_A2B072888C4FC193 (instructor_id), INDEX IDX_A2B072885200282E (formation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE formations_user (formations_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_D653FD6A3BF5B0C2 (formations_id), INDEX IDX_D653FD6AA76ED395 (user_id), PRIMARY KEY(formations_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE documents ADD CONSTRAINT FK_A2B072888C4FC193 FOREIGN KEY (instructor_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE documents ADD CONSTRAINT FK_A2B072885200282E FOREIGN KEY (formation_id) REFERENCES formations (id)');
        $this->addSql('ALTER TABLE formations_user ADD CONSTRAINT FK_D653FD6A3BF5B0C2 FOREIGN KEY (formations_id) REFERENCES formations (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE formations_user ADD CONSTRAINT FK_D653FD6AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE formations ADD site_id INT NOT NULL');
        $this->addSql('ALTER TABLE formations ADD CONSTRAINT FK_40902137F6BD1646 FOREIGN KEY (site_id) REFERENCES site (id)');
        $this->addSql('CREATE INDEX IDX_40902137F6BD1646 ON formations (site_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE documents DROP FOREIGN KEY FK_A2B072888C4FC193');
        $this->addSql('ALTER TABLE documents DROP FOREIGN KEY FK_A2B072885200282E');
        $this->addSql('ALTER TABLE formations_user DROP FOREIGN KEY FK_D653FD6A3BF5B0C2');
        $this->addSql('ALTER TABLE formations_user DROP FOREIGN KEY FK_D653FD6AA76ED395');
        $this->addSql('DROP TABLE documents');
        $this->addSql('DROP TABLE formations_user');
        $this->addSql('ALTER TABLE formations DROP FOREIGN KEY FK_40902137F6BD1646');
        $this->addSql('DROP INDEX IDX_40902137F6BD1646 ON formations');
        $this->addSql('ALTER TABLE formations DROP site_id');
    }
}
