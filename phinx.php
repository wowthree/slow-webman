<?php
return [
    "paths" => [
        "migrations" => "database/migrations",
        "seeds"      => "database/seeds"
    ],
    "environments" => [
        "default_migration_table" => "phinxlog",
        "default_environment"     => "dev",
        "dev" => [
            "adapter" => "mysql",
            "host"    => "127.0.0.1",
            "name"    => "slow_webman",
            "user"    => "root",
            "pass"    => "root",
            "port"    => 3306,
            "charset" => "utf8mb4",
            "collation" => "utf8mb4_unicode_ci",
            "table_prefix" => "",
        ],
    ]
];
