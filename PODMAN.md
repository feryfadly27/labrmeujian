# Podman Development

The project database runs in its own MariaDB container and does not share the existing OJS database.

## Start the database

Copy `.env.example` to `.env` and replace both passwords with local-only values. The `.env` file is ignored by Git.

```sh
podman compose up -d db
podman compose ps
```

The database is exposed only on `127.0.0.1:3307`.

## Initialize the schema

Run the schema against the project database after the container is healthy:

```sh
podman exec -i jadwal-lab-db sh -c 'mariadb -u root -p"$MARIADB_ROOT_PASSWORD" jadwal_lab' < database/schema.sql
```

The root password is read inside the container. Do not put it in source files or shell history.

## Run PHP

Install PHP dependencies before the first run:

```sh
composer install --no-interaction --prefer-dist
```

Export the values from `.env` in the shell, then start the public document root:

```sh
php -S 127.0.0.1:8084 -t public
```

The application reads `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` from the process environment.
