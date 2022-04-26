<?php
$config = new M6Web\CS\Config\BedrockStreaming();
$config
    ->getFinder()
    ->in([
        __DIR__.'/src',
    ]);

return $config;
