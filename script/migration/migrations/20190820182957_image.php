<?php

use Phinx\Migration\AbstractMigration;

class Image extends AbstractMigration
{
    public function up()
    {
        $this->query("
            CREATE TABLE track_image (
                id CHAR(36) NOT NULL COMMENT '(DC2Type:guid)',
                track_id CHAR(36) NOT NULL COMMENT '(DC2Type:guid)',
                send_by_id INT NOT NULL,
                filepath VARCHAR(255) NOT NULL,
                INDEX IDX_TRACK_ID (track_id),
                INDEX IDX_SEND_BY (send_by_id),
                PRIMARY KEY(id)
            )
            DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB
        ");

        $this->query("
            ALTER TABLE track_image ADD CONSTRAINT FK_TF_TRACK_ID FOREIGN KEY (track_id) REFERENCES track (id)
        ");

        $this->query("
            ALTER TABLE track_image ADD CONSTRAINT FK_TF_SEND_BY_ID FOREIGN KEY (send_by_id) REFERENCES `user` (id)
        ");
    }

    public function down()
    {
        $this->query("ALTER TABLE track_image DROP CONSTRAINT FK_TF_SEND_BY_ID");
        $this->query("ALTER TABLE track_image DROP CONSTRAINT FK_TF_TRACK_ID");

        $this->query("DROP TABLE track_image");
    }
}
