<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class PlaceAttraction extends AbstractMigration
{
    public function up(): void
    {
        $this->query('ALTER TABLE `place` ADD COLUMN is_attraction TINYINT(1) NOT NULL DEFAULT 0');
    }

    public function down(): void
    {
        $this->query('ALTER TABLE `place` DROP COLUMN is_attraction');
    }
}
