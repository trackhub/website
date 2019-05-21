<?php

use Phinx\Migration\AbstractMigration;

class TrackUphillsDownhill extends AbstractMigration
{
    public function up()
    {
        $this->query("
            CREATE TABLE track_track (
                track_source CHAR(36) NOT NULL COMMENT '(DC2Type:guid)', 
                track_target CHAR(36) NOT NULL COMMENT '(DC2Type:guid)', 
                INDEX IDX_A6145A011748BE9A (track_source), 
                INDEX IDX_A6145A01EADEE15 (track_target), 
                PRIMARY KEY(track_source, track_target)
            )
             DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB
        ");
    }

    public function down()
    {
        $this->query('DROP TABLE track_track');
    }
}
