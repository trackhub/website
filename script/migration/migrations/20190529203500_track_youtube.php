<?php

use Phinx\Migration\AbstractMigration;

class TrackYoutube extends AbstractMigration
{
    public function up()
    {
        $this->query("
            ALTER TABLE track ADD videos_youtube_id CHAR(36) DEFAULT NULL COMMENT '(DC2Type:guid)'
        ");
    }

    public function down()
    {
        $this->query("ALTER TABLE track DROP videos_youtube_id");
    }
}
