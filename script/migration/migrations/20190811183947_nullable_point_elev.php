<?php

use Phinx\Migration\AbstractMigration;

class NullablePointElev extends AbstractMigration
{
    public function up()
    {
        $this->query("ALTER TABLE point CHANGE elevation elevation DOUBLE PRECISION DEFAULT NULL");
        $this->query("ALTER TABLE version_waypoint CHANGE elevation elevation DOUBLE PRECISION DEFAULT NULL");
    }

    public function down()
    {
        $this->query("ALTER TABLE point CHANGE elevation elevation DOUBLE PRECISION");
        $this->query("ALTER TABLE version_waypoint CHANGE elevation elevation DOUBLE PRECISION");
    }
}
