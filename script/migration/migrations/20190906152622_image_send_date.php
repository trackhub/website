<?php

use Phinx\Migration\AbstractMigration;

class ImageSendDate extends AbstractMigration
{
    public function up()
    {
        $this->query("ALTER TABLE track_image ADD created_at DATETIME NOT NULL");
        $this->query("UPDATE track_image SET created_at = CURRENT_TIMESTAMP");
    }

    public function down()
    {
        $this->query("ALTER TABLE track_image DROP created_at");
    }
}
