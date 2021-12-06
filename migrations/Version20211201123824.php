<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211201123824 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE picture DROP CONSTRAINT FK_16DB4F89D07ECCB6');
        $this->addSql('ALTER TABLE picture ADD CONSTRAINT FK_16DB4F89D07ECCB6 FOREIGN KEY (advert_id) REFERENCES advert (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE picture DROP CONSTRAINT fk_16db4f89d07eccb6');
        $this->addSql('ALTER TABLE picture ADD CONSTRAINT fk_16db4f89d07eccb6 FOREIGN KEY (advert_id) REFERENCES advert (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
