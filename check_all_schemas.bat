@echo off
set PGPASSWORD=master10
echo ### SCHEMAS ###
psql -h 127.0.0.1 -p 5432 -U postgres -d sis-ponto -P pager=off -c "SELECT nspname FROM pg_namespace WHERE nspname NOT LIKE 'pg_%%' AND nspname <> 'information_schema';"
echo ### REGISTROS POR SCHEMA ###
psql -h 127.0.0.1 -p 5432 -U postgres -d sis-ponto -P pager=off -c "SELECT table_schema, count(*) FROM information_schema.tables WHERE table_name = 'registros' GROUP BY table_schema;"
