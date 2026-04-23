@echo off
set PGPASSWORD=master10
psql -h 127.0.0.1 -p 5432 -U postgres -d sis-ponto -c "SELECT count(*) FROM ponto.funcionarios; SELECT count(*) FROM ponto.horarios;"
