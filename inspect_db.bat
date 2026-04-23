@echo off
set PGPASSWORD=master10
echo ### BANCOS DE DADOS ###
psql -h 127.0.0.1 -p 5432 -U postgres -P pager=off -c "SELECT datname FROM pg_database WHERE datistemplate = false;"
echo ### DETALHES DO REGISTRO ###
psql -h 127.0.0.1 -p 5432 -U postgres -d sis-ponto -P pager=off -c "SELECT * FROM ponto.registros LIMIT 1;"
