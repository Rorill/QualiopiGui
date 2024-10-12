<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241010150206 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE documents (id INT AUTO_INCREMENT NOT NULL, formation_id INT NOT NULL, category_id INT NOT NULL, formateur_id INT NOT NULL, title VARCHAR(255) NOT NULL, uploaded_at DATETIME NOT NULL, INDEX IDX_A2B072885200282E (formation_id), INDEX IDX_A2B0728812469DE2 (category_id), INDEX IDX_A2B07288155D8F51 (formateur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE formations (id INT AUTO_INCREMENT NOT NULL, location_id INT DEFAULT NULL, starting_date DATE NOT NULL, ending_date DATE NOT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_4090213764D218E (location_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE formations_user (formations_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_D653FD6A3BF5B0C2 (formations_id), INDEX IDX_D653FD6AA76ED395 (user_id), PRIMARY KEY(formations_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE location (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, city VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, first_name VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE documents ADD CONSTRAINT FK_A2B072885200282E FOREIGN KEY (formation_id) REFERENCES formations (id)');
        $this->addSql('ALTER TABLE documents ADD CONSTRAINT FK_A2B0728812469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE documents ADD CONSTRAINT FK_A2B07288155D8F51 FOREIGN KEY (formateur_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE formations ADD CONSTRAINT FK_4090213764D218E FOREIGN KEY (location_id) REFERENCES location (id)');
        $this->addSql('ALTER TABLE formations_user ADD CONSTRAINT FK_D653FD6A3BF5B0C2 FOREIGN KEY (formations_id) REFERENCES formations (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE formations_user ADD CONSTRAINT FK_D653FD6AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE documents DROP FOREIGN KEY FK_A2B072885200282E');
        $this->addSql('ALTER TABLE documents DROP FOREIGN KEY FK_A2B0728812469DE2');
        $this->addSql('ALTER TABLE documents DROP FOREIGN KEY FK_A2B07288155D8F51');
        $this->addSql('ALTER TABLE formations DROP FOREIGN KEY FK_4090213764D218E');
        $this->addSql('ALTER TABLE formations_user DROP FOREIGN KEY FK_D653FD6A3BF5B0C2');
        $this->addSql('ALTER TABLE formations_user DROP FOREIGN KEY FK_D653FD6AA76ED395');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE documents');
        $this->addSql('DROP TABLE formations');
        $this->addSql('DROP TABLE formations_user');
        $this->addSql('DROP TABLE location');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
