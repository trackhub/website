<?php

use Phinx\Migration\AbstractMigration;

class KeepVersionOnUserRemove extends AbstractMigration
{
    public function up()
    {
        /**
         * Make send_by_id default null
         */
        $this->query("
            ALTER TABLE version
            ALTER COLUMN send_by_id
            SET DEFAULT NULL
        ");
        $this->query("
            ALTER TABLE track
            MODIFY send_by_id
            INT DEFAULT NULL
        ");

        /**
         * Remove send_by_id FK
         */
        $this->query("
            ALTER TABLE version 
            DROP FOREIGN KEY FK_BF1CD3C3C3852542
        ");
        $this->query("
            ALTER TABLE track 
            DROP FOREIGN KEY FK_send_by_id
        ");

        /**
         * Restore modified FK
         */
        $this->query("
            ALTER TABLE version
	            ADD CONSTRAINT FK_BF1CD3C3C3852542
		        FOREIGN KEY(send_by_id) REFERENCES `user` (id)
			    ON DELETE SET NULL
        ");
        $this->query("
            ALTER TABLE track
	            ADD CONSTRAINT FK_send_by_id
		        FOREIGN KEY(send_by_id) REFERENCES `user` (id)
			    ON DELETE SET NULL
        ");
    }

    public function down()
    {
        /**
         * Remove send_by_id FK
         */
        $this->query("
            ALTER TABLE version 
            DROP FOREIGN KEY FK_BF1CD3C3C3852542
        ");
        $this->query("
            ALTER TABLE track 
            DROP FOREIGN KEY FK_send_by_id
        ");

        /**
         * Restore modified FK
         */
        $this->query("
            ALTER TABLE version
	            ADD CONSTRAINT FK_BF1CD3C3C3852542
		        FOREIGN KEY(send_by_id) REFERENCES `user` (id)
        ");
        $this->query("
            ALTER TABLE track
	            ADD CONSTRAINT FK_send_by_id
		        FOREIGN KEY(send_by_id) REFERENCES `user` (id)
        ");

        /**
         * Make send_by_id default not null
         */
        $this->query("
            ALTER TABLE track
            MODIFY send_by_id
            INT NOT NULL
        ");
    }
}
