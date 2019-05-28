<?php

use Phinx\Migration\AbstractMigration;

class TrackVisibility extends AbstractMigration
{
    public function up()
    {
        $this->query("ALTER TABLE track ADD visibility TINYINT(1) NOT NULL, CHANGE name name VARCHAR(255) DEFAULT 0");
    }

    public function down()
    {
        $this->query("ALTER TABLE track DROP visibility");
    }
}
