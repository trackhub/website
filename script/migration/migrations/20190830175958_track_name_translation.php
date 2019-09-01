<?php

use Phinx\Migration\AbstractMigration;

class TrackNameTranslation extends AbstractMigration
{
    public function up()
    {
        $this->query("ALTER TABLE track CHANGE `name` name_en VARCHAR(255) DEFAULT NULL");

        $this->query("ALTER TABLE track ADD name_bg VARCHAR(255) DEFAULT NULL");
    }

    public function down()
    {
        $this->query("ALTER TABLE track CHANGE name_en `name` VARCHAR(255) DEFAULT NULL");

        $this->query("ALTER TABLE track DROP COLUMN name_bg");
    }
}
