# Database migrations – VidNect Engine

This directory contains raw SQL migrations for VidNect Engine.

## Order
Migrations must be executed in numeric order:
001_*.sql
002_*.sql
...

## Environment
- MariaDB / MySQL
- utf8mb4 / InnoDB

## Execution
For now, migrations are executed manually:
- via phpMyAdmin
- or via mysql CLI

A CLI migration tool may be added later.
