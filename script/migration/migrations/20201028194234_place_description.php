<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class PlaceDescription extends AbstractMigration
{
    public function up(): void
    {
        $this->query("ALTER TABLE place ADD description_en TEXT DEFAULT NULL");
        $this->query("ALTER TABLE place ADD description_bg TEXT DEFAULT NULL");
    }

    public function down(): void
    {
        $this->query("ALTER TABLE place DROP COLUMN description_en");
        $this->query("ALTER TABLE place DROP COLUMN description_bg");
    }
}
