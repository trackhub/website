<?php

use Phinx\Seed\AbstractSeed;

class CleanerSeeder extends AbstractSeed
{
    public function run()
    {
        /* Delete previous data */
        $this->query("UPDATE track_file SET version_id = NULL");
        $this->query("UPDATE version SET file_id = NULL");
        $this->query("DELETE FROM track_slug");
        $this->query("DELETE FROM track_file");
        $this->query("DELETE FROM track_image");
        $this->query("DELETE FROM place_image");
        $this->query("DELETE FROM version_waypoint");
        $this->query("DELETE FROM point");
        $this->query("DELETE FROM optimized_point");
        $this->query("DELETE FROM version");
        $this->query("DELETE FROM track");
        $this->query("DELETE FROM place");
        $this->query("DELETE FROM user");
        $this->query("ALTER TABLE user AUTO_INCREMENT = 1");
    }
}
