<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class UserDelete extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("
            ALTER TABLE user ADD COLUMN `deletion` tinyint NOT NULL DEFAULT 0
        ");
    }

    public function down(): void
    {
        $this->execute("ALTER TABLE user DROP COLUMN `deletion`");
    }
}
