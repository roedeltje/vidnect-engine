# Database migrations — VidNect Engine

Deze map bevat SQL migrations voor de VidNect Engine database.

## Belangrijke regels

- Migrations zijn **forward-only** (geen rollback-systeem in v0.1).
- **Pas nooit** een bestaande migration aan als die al in een omgeving is uitgevoerd.
  - Nieuwe wijziging? → Nieuwe migration met oplopend nummer.
- Nummering: `001_*.sql`, `002_*.sql`, `003_*.sql`, ...
- Elke migration hoort:
  - **Wat** verandert er?
  - **Waarom** is dit nodig?
  - **Impact** (downtime? lock? data changes?)
  - **Checks** (SQL om te verifiëren)

## Migraties uitvoeren (handmatig)

1. Maak (indien nodig) eerst een backup:
   - `mysqldump ... > backup.sql`

2. Log in op de juiste database:
   - `mysql -u <user> -p <database>`

3. Voer de migration uit:
   - `SOURCE /full/path/to/migrations/002_rooms_soft_close.sql;`

4. Verifieer met de “Checks” uit de migration.

5. Werk hieronder de Applied log bij.

## Migrations overzicht

| Nr  | Bestand                     | Beschrijving                                  |
|-----|-----------------------------|----------------------------------------------|
| 001 | 001_rooms.sql               | Init rooms tabel (room_id, name, timestamps) |
| 002 | 002_rooms_soft_close.sql    | Room lifecycle: status + closed_at           |
| 003 | 003_room_tokens.sql | Stateful tokens: room_tokens table (hash + expiry)   |

## Applied log

> Vul na uitvoeren de datum + initialen in.
> Voorbeeld: `2026-01-04 (RD)`.

### Local (dev)
- [ ] 001_rooms.sql — ____-__-__ (__)
- [ ] 002_rooms_soft_close.sql — ____-__-__ (__)

### Staging
- [ ] 001_rooms.sql — ____-__-__ (__)
- [ ] 002_rooms_soft_close.sql — ____-__-__ (__)

### Production
- [ ] 001_rooms.sql — ____-__-__ (__)
- [ ] 002_rooms_soft_close.sql — ____-__-__ (__)
