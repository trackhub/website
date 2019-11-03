<?php

require __DIR__ . '/src/WebTestCase.php';

(new \Symfony\Component\Dotenv\Dotenv(false))->loadEnv(
    __DIR__ . '/../../.env'
);
