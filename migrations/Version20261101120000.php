<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * El email de una cuenta pasa a ser único (antes era un índice normal).
 *
 * Si ya hay emails repetidos la migración se detiene en lugar de fallar a
 * mitad con un error de MySQL: cuál de las cuentas se conserva es una
 * decisión de negocio, no algo que una migración pueda adivinar.
 */
final class Version20261101120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Make user.email_address unique';
    }

    public function preUp(Schema $schema): void
    {
        $duplicated = (int) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM (SELECT email_address FROM user GROUP BY email_address HAVING COUNT(*) > 1) AS duplicated'
        );

        $this->abortIf(
            $duplicated > 0,
            sprintf(
                'Hay %d email(s) con más de una cuenta en la tabla user. Resuélvelos (SELECT email_address, COUNT(*) FROM user GROUP BY email_address HAVING COUNT(*) > 1) antes de migrar.',
                $duplicated
            )
        );
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP INDEX email_address_idx ON user');
        $this->addSql('CREATE UNIQUE INDEX email_address_uniq ON user (email_address)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX email_address_uniq ON user');
        $this->addSql('CREATE INDEX email_address_idx ON user (email_address)');
    }
}
