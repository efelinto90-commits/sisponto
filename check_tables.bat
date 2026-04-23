@echo off
set PGPASSWORD=master10
psql -h 127.0.0.1 -p 5432 -U postgres -d sis-ponto -c "\d ponto.registros"
psql -h 127.0.0.1 -p 5432 -U postgres -d sis-ponto -c "\d ponto.horarios"
