<?php
declare(strict_types=1);

namespace Marein\LockDoctrineMigrationsBundle\Tests\Integration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Migration extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $table = $schema->createTable('test');

        $table->addColumn('id', 'integer', ['autoincrement' => true]);

        $table->setPrimaryKey(['id']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('test');
    }
}
