# Storyclash Database CLI

Command line tool that copies a table entry (incl. strictly required data) and accepts an option that would copy additional entries.

## Technologies, Languages
* PHP, composer, PHPUnit, XAMPP, phpMyAdmin

## Usage
* php scCli setupDatabases - to setup live and dev databases and create data for the live-database
* php scCli copy 1 > copy feed entry with ID 1 and incl. all sources
* php scCli copy 1 --only=instagram > copy feed entry with ID 1 and respective entry from instagram_sources
* php scCli copy 1 --only=tiktok --include-posts=3 > copy feed entry with ID 1, respective entry from titok_sources and 3 posts

## Unit tests
* ./vendor/bin/phpunit --testdox Tests
