<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240419120020 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE product (id CHAR(36) NOT NULL COMMENT \'(DC2Type:vo_id)\', user_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:vo_id)\', created_on DATETIME NOT NULL COMMENT \'(DC2Type:vo_date_time)\', updated_on DATETIME NOT NULL COMMENT \'(DC2Type:vo_date_time)\', name VARCHAR(255) NOT NULL COMMENT \'(DC2Type:vo_name)\', description LONGTEXT NOT NULL COMMENT \'(DC2Type:vo_description)\', base_price VARCHAR(255) NOT NULL COMMENT \'(DC2Type:vo_amount)\', price_with_iva VARCHAR(255) NOT NULL COMMENT \'(DC2Type:vo_amount)\', iva INT NOT NULL COMMENT \'(DC2Type:vo_iva)\', INDEX IDX_D34A04ADA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id CHAR(36) NOT NULL COMMENT \'(DC2Type:vo_id)\', name VARCHAR(255) NOT NULL COMMENT \'(DC2Type:vo_name)\', email_address VARCHAR(255) NOT NULL COMMENT \'(DC2Type:vo_email_address)\', password VARCHAR(255) NOT NULL COMMENT \'(DC2Type:vo_password_hash)\', created_on DATETIME NOT NULL COMMENT \'(DC2Type:vo_date_time)\', updated_on DATETIME NOT NULL COMMENT \'(DC2Type:vo_date_time)\', last_access_on DATETIME DEFAULT NULL COMMENT \'(DC2Type:vo_date_time)\', roles JSON NOT NULL COMMENT \'(DC2Type:vo_rol_collection)\', last_password_update DATETIME DEFAULT NULL COMMENT \'(DC2Type:vo_date_time)\', activation_token_selector VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:vo_clear_text_token)\', activation_token_verifier VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:vo_token_hash)\', recovery_token_selector VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:vo_clear_text_token)\', recovery_token_verifier VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:vo_token_hash)\', recovery_token_valid_until DATETIME DEFAULT NULL COMMENT \'(DC2Type:vo_date_time)\', discr VARCHAR(255) NOT NULL, INDEX email_address_idx (email_address), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_administrator (id CHAR(36) NOT NULL COMMENT \'(DC2Type:vo_id)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_web (id CHAR(36) NOT NULL COMMENT \'(DC2Type:vo_id)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04ADA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_administrator ADD CONSTRAINT FK_B651AB01BF396750 FOREIGN KEY (id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_web ADD CONSTRAINT FK_FEDF59DABF396750 FOREIGN KEY (id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04ADA76ED395');
        $this->addSql('ALTER TABLE user_administrator DROP FOREIGN KEY FK_B651AB01BF396750');
        $this->addSql('ALTER TABLE user_web DROP FOREIGN KEY FK_FEDF59DABF396750');
        $this->addSql('DROP TABLE product');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_administrator');
        $this->addSql('DROP TABLE user_web');
    }
}
