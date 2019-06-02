<?php

use Phinx\Migration\AbstractMigration;

class Youtube extends AbstractMigration
{
    public function up()
    {
        $this->query("
            CREATE TABLE video_youtube (
                id CHAR(36) NOT NULL COMMENT '(DC2Type:guid)', 
                track_id CHAR(36) DEFAULT NULL COMMENT '(DC2Type:guid)', 
                link VARCHAR(255) NOT NULL, 
                INDEX IDX_159570995ED23C43 (track_id), PRIMARY KEY(id)
            )
            DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB
        ");
    }

    public function down()
    {
        $this->query("DROP TABLE video_youtube");
    }
}
