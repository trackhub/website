<?php

use Phinx\Migration\AbstractMigration;

class OptimizedVersions extends AbstractMigration
{
    public function up()
    {
        $this->query("ALTER TABLE optimized_point ADD version_index INT NOT NULL");
    }

    public function down()
    {
        $this->query("ALTER TABLE optimized_point DROP version_index");
    }
}
