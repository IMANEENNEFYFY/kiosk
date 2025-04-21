<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250420200529 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE espace (id INT AUTO_INCREMENT NOT NULL, identifiant VARCHAR(50) NOT NULL, nom VARCHAR(100) NOT NULL, description LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE categorie ADD espace_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE categorie ADD CONSTRAINT FK_497DD634B6885C6C FOREIGN KEY (espace_id) REFERENCES espace (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_497DD634B6885C6C ON categorie (espace_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commande CHANGE ticket_path ticket_path VARCHAR(255) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE produit ADD espace_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE produit ADD CONSTRAINT FK_29A5EC27B6885C6C FOREIGN KEY (espace_id) REFERENCES espace (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_29A5EC27B6885C6C ON produit (espace_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE categorie DROP FOREIGN KEY FK_497DD634B6885C6C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE produit DROP FOREIGN KEY FK_29A5EC27B6885C6C
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE espace
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_497DD634B6885C6C ON categorie
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE categorie DROP espace_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commande CHANGE ticket_path ticket_path VARCHAR(255) DEFAULT NULL COMMENT 'Chemin vers le ticket PDF'
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_29A5EC27B6885C6C ON produit
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE produit DROP espace_id
        SQL);
    }
}
