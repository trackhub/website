<?php

use Phinx\Migration\AbstractMigration;

class TrackDescription extends AbstractMigration
{
    public function up()
    {
        $this->query("ALTER TABLE track ADD description_en TEXT DEFAULT NULL");
        $this->query("ALTER TABLE track ADD description_bg TEXT DEFAULT NULL");
    }

    public function down()
    {
        $this->query("ALTER TABLE track DROP COLUMN description_en");
        $this->query("ALTER TABLE track DROP COLUMN description_bg");
    }
}
