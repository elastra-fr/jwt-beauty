<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240523190128 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE regions (id INT AUTO_INCREMENT NOT NULL, region_name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE salon ADD CONSTRAINT FK_F268F417A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_F268F417A76ED395 ON salon (user_id)');
        $this->addSql('ALTER TABLE turnover ADD CONSTRAINT FK_638A62C4C91BDE4 FOREIGN KEY (salon_id) REFERENCES salon (id)');
        $this->addSql('CREATE INDEX IDX_638A62C4C91BDE4 ON turnover (salon_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE regions');
        $this->addSql('ALTER TABLE salon DROP FOREIGN KEY FK_F268F417A76ED395');
        $this->addSql('DROP INDEX IDX_F268F417A76ED395 ON salon');
        $this->addSql('ALTER TABLE turnover DROP FOREIGN KEY FK_638A62C4C91BDE4');
        $this->addSql('DROP INDEX IDX_638A62C4C91BDE4 ON turnover');
    }
}
