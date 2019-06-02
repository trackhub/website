<?php

use Phinx\Migration\AbstractMigration;

class TrackYoutubeRelation extends AbstractMigration
{
    public function up()
    {
        $this->query("
            ALTER TABLE video_youtube ADD CONSTRAINT FK_159570995ED23C43 FOREIGN KEY (track_id) REFERENCES track (id)
        ");
    }

    public function down()
    {
        $this->query("ALTER TABLE video_youtube DROP FOREIGN KEY FK_159570995ED23C43");
    }
}
