<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211130210743 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE book CHANGE librairies_id librairies_id INT DEFAULT NULL, CHANGE subtitle subtitle VARCHAR(255) DEFAULT NULL, CHANGE editor editor VARCHAR(255) DEFAULT NULL, CHANGE publish_date publish_date DATETIME DEFAULT NULL, CHANGE language language VARCHAR(255) DEFAULT NULL, CHANGE image image VARCHAR(255) DEFAULT NULL, CHANGE description description VARCHAR(4000) DEFAULT NULL');
        $this->addSql('ALTER TABLE book RENAME INDEX fk_cbe5a331b52a5368 TO IDX_CBE5A331B52A5368');
        $this->addSql('ALTER TABLE opinion CHANGE note note INT DEFAULT NULL, CHANGE comment comment INT DEFAULT NULL');
        $this->addSql('ALTER TABLE post RENAME INDEX fk_5a8a6c8d67b3b43d TO IDX_5A8A6C8D67B3B43D');
        $this->addSql('ALTER TABLE user ADD library_id INT DEFAULT NULL, CHANGE roles roles JSON NOT NULL, CHANGE birth_date birth_date DATETIME DEFAULT NULL, CHANGE pseudo pseudo VARCHAR(255) DEFAULT NULL, CHANGE picture picture VARCHAR(255) DEFAULT NULL, CHANGE gender gender VARCHAR(255) DEFAULT NULL, CHANGE phone_number phone_number VARCHAR(255) DEFAULT NULL, CHANGE nb_follower nb_follower INT DEFAULT NULL, CHANGE nb_follow nb_follow INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649FE2541D7 FOREIGN KEY (library_id) REFERENCES library (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON user (email)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649FE2541D7 ON user (library_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE book CHANGE librairies_id librairies_id INT DEFAULT NULL, CHANGE subtitle subtitle VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE editor editor VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE publish_date publish_date DATETIME DEFAULT \'NULL\', CHANGE language language VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE image image VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE description description VARCHAR(4000) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE book RENAME INDEX idx_cbe5a331b52a5368 TO FK_CBE5A331B52A5368');
        $this->addSql('ALTER TABLE opinion CHANGE note note INT DEFAULT NULL, CHANGE comment comment INT DEFAULT NULL');
        $this->addSql('ALTER TABLE post RENAME INDEX idx_5a8a6c8d67b3b43d TO FK_5A8A6C8D67B3B43D');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649FE2541D7');
        $this->addSql('DROP INDEX UNIQ_8D93D649E7927C74 ON user');
        $this->addSql('DROP INDEX UNIQ_8D93D649FE2541D7 ON user');
        $this->addSql('ALTER TABLE user DROP library_id, CHANGE roles roles LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_bin`, CHANGE birth_date birth_date DATETIME DEFAULT \'NULL\', CHANGE pseudo pseudo VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE picture picture VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE gender gender VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE phone_number phone_number VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE nb_follower nb_follower INT DEFAULT NULL, CHANGE nb_follow nb_follow INT DEFAULT NULL');
    }
}
