<?php

use Phinx\Migration\AbstractMigration;

class AddTrackDatetime extends AbstractMigration
{
    public function up()
    {
        $this->query("
            ALTER TABLE track ADD created_at DATETIME NOT NULL, CHANGE name name VARCHAR(255) DEFAULT NULL
        ");

        $this->query("
            UPDATE track 
            INNER JOIN version ON version.track_id = track.id
            INNER JOIN track_file ON track_file.version_id = version.id
            
            SET track.created_at = track_file.created_at 
        ");
    }

    public function down()
    {
        $this->query("ALTER TABLE track DROP created_at");
    }
}
