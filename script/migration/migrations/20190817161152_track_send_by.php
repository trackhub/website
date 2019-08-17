<?php

use Phinx\Migration\AbstractMigration;

class TrackSendBy extends AbstractMigration
{
    public function up()
    {
        $this->query("ALTER TABLE track ADD send_by_id INT DEFAULT NULL");

        $this->query("
            UPDATE
                track
            SET
                send_by_id = (SELECT send_by_id FROM version WHERE track_id = track.id LIMIT 1)
        ");

        $this->query("ALTER TABLE track CHANGE send_by_id send_by_id INT NOT NULL");
        $this->query("ALTER TABLE track ADD CONSTRAINT FK_send_by_id FOREIGN KEY (send_by_id) REFERENCES `user` (id)");
        $this->query("CREATE INDEX IDX_SEND_BY_ID ON track (send_by_id)");
    }

    public function down()
    {
        $this->query("ALTER TABLE track DROP CONSTRAINT FK_send_by_id");
        $this->query("ALTER TABLE track DROP send_by_id");
    }
}
