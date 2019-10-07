<?php

use Phinx\Migration\AbstractMigration;

class PlaceImage extends AbstractMigration
{
    public function up()
    {
        $this->query("
            CREATE TABLE place_image (
                id CHAR(36) NOT NULL COMMENT '(DC2Type:guid)',
                place_id CHAR(36) NOT NULL COMMENT '(DC2Type:guid)',
                send_by_id INT NOT NULL,
                filepath VARCHAR(255) NOT NULL,
                created_at DATETIME NOT NULL,
                INDEX IDX_PLACE_IMAGE_PLACE_ID (place_id),
                INDEX IDX_PLACE_IMAGE_SEND_BY (send_by_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB
        ");

        $this->query("ALTER TABLE place_image ADD CONSTRAINT FK_PI_PLACE_ID FOREIGN KEY (place_id) REFERENCES place (id)");
        $this->query("ALTER TABLE place_image ADD CONSTRAINT FK_PI_SEND_BY_ID FOREIGN KEY (send_by_id) REFERENCES `user` (id)");
    }

    public function down()
    {
        $this->query("DROP TABLE place_image");
    }
}
