<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240403082101 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE `items` (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `order_items` (id INT AUTO_INCREMENT NOT NULL, order_id INT NOT NULL, item_id INT NOT NULL, quantity VARCHAR(100) NOT NULL, INDEX IDX_62809DB0FCDAEAAA (order_id), INDEX IDX_62809DB055E38587 (item_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `orders` (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, delivery_address VARCHAR(255) NOT NULL, delivery_option VARCHAR(100) NOT NULL, status ENUM(\'processing\', \'delivered\', \'delayed\') NOT NULL, delivery_date DATETIME NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE `order_items` ADD CONSTRAINT FK_62809DB0FCDAEAAA FOREIGN KEY (order_id) REFERENCES `orders` (id)');
        $this->addSql('ALTER TABLE `order_items` ADD CONSTRAINT FK_62809DB055E38587 FOREIGN KEY (item_id) REFERENCES `items` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `order_items` DROP FOREIGN KEY FK_62809DB0FCDAEAAA');
        $this->addSql('ALTER TABLE `order_items` DROP FOREIGN KEY FK_62809DB055E38587');
        $this->addSql('DROP TABLE `items`');
        $this->addSql('DROP TABLE `order_items`');
        $this->addSql('DROP TABLE `orders`');
    }
}
