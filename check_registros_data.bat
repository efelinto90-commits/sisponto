@echo off
set PGPASSWORD=master10
psql -h 127.0.0.1 -p 5432 -U postgres -d sis-ponto -P pager=off -c "SELECT count(*) FROM ponto.registros WHERE data BETWEEN '2026-03-01' AND '2026-03-31';"
psql -h 127.0.0.1 -p 5432 -U postgres -d sis-ponto -P pager=off -c "SELECT * FROM ponto.registros ORDER BY data DESC LIMIT 10;"
