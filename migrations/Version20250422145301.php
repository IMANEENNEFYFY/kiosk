<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250422145301 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE carte_prepayee (id INT AUTO_INCREMENT NOT NULL, code_carte VARCHAR(50) NOT NULL, nom VARCHAR(100) NOT NULL, prenom VARCHAR(100) NOT NULL, solde DOUBLE PRECISION NOT NULL, actif TINYINT(1) NOT NULL, date_creation DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE categorie CHANGE espace_id espace_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commande CHANGE ticket_path ticket_path VARCHAR(255) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE produit CHANGE espace_id espace_id INT DEFAULT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP TABLE carte_prepayee
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE categorie CHANGE espace_id espace_id INT DEFAULT 1
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commande CHANGE ticket_path ticket_path VARCHAR(255) DEFAULT NULL COMMENT 'Chemin vers le ticket PDF'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE produit CHANGE espace_id espace_id INT DEFAULT 1
        SQL);
    }
}
