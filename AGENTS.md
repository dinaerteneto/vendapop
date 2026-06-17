# Regras do projeto

## Database

- **NUNCA** usar `php artisan migrate:fresh`. Este comando apaga todas as tabelas e recria do zero, destruindo os dados de desenvolvimento.
- **NUNCA** usar `docker compose down -v`. A flag `-v` destrói o volume `moda_db_data`, apagando o banco MySQL.
- Para rodar migrations pendentes, usar apenas `php artisan migrate`.
- Para popular dados, usar `php artisan db:seed` ou seeders específicos (`php artisan db:seed --class=NomeSeeder`).
