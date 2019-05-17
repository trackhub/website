<?php

use Phinx\Migration\AbstractMigration;

class ElevationData extends AbstractMigration
{
    public function up()
    {
        $this->query("
            ALTER TABLE version 
            ADD positive_elevation INT NOT NULL, 
            ADD negative_elevation INT NOT NULL
        ");
    }

    public function down()
    {
        $this->query("ALTER TABLE version DROP positive_elevation");
        $this->query("ALTER TABLE version DROP negative_elevation");
    }
}
