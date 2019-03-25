<?php

return [
    'default' => 'local',
    'disks' => [
        'local' => [
            'driver' => 'local',
            'root' => getcwd() . DIRECTORY_SEPARATOR . 'storage',
        ],
    ],
];