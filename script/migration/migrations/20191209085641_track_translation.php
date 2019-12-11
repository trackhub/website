<?php

use Phinx\Migration\AbstractMigration;

class TrackTranslation extends AbstractMigration
{
    public function up()
    {
        /**
         * Drop name related columns in the track table
         */
        $this->query("
            ALTER TABLE track
                DROP COLUMN name_en;

            ALTER TABLE track
                DROP COLUMN name_bg;
                
            ALTER TABLE track
                DROP COLUMN description_en;
        
            ALTER TABLE track
                DROP COLUMN description_bg;
        ");

        /**
         * Create a new table
         */
        $this->query("
            CREATE TABLE track_tr
            (
                id INT auto_increment,
                track_id CHAR(36) NOT NULL COMMENT '(DC2Type:guid)',
                language_id INT NOT NULL,
                name VARCHAR(255) DEFAULT NULL,
                description TEXT DEFAULT NULL,
                CONSTRAINT track_tr_pk
		            PRIMARY KEY (id),
	            CONSTRAINT track_tr_track_id_fk
		            FOREIGN KEY (track_id) REFERENCES track (id),
		        CONSTRAINT track_tr_language_id_fk
		            FOREIGN KEY (language_id) REFERENCES language (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB;
        ");
    }

    public function down()
    {
        $this->query("                
            DROP TABLE track_tr
        ");

        $this->query("
            ALTER TABLE track
	            ADD name_en VARCHAR(255) DEFAULT NULL;

            ALTER TABLE track
	            ADD name_bg VARCHAR(255) DEFAULT NULL;
	            
            ALTER TABLE track
                ADD description_en TEXT DEFAULT NULL;

            ALTER TABLE track
                ADD description_bg TEXT DEFAULT NULL;
        ");
    }
}
