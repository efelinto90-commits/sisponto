@echo off
set PGPASSWORD=master10
echo ### CONSTRAINTS REGISTROS ###
psql -h 127.0.0.1 -p 5432 -U postgres -d sis-ponto -P pager=off -c "SELECT conname, pg_get_constraintdef(oid) FROM pg_constraint WHERE conrelid = 'ponto.registros'::regclass;"
echo ### INDEXES REGISTROS ###
psql -h 127.0.0.1 -p 5432 -U postgres -d sis-ponto -P pager=off -c "SELECT indexname, indexdef FROM pg_indexes WHERE schemaname = 'ponto' AND tablename = 'registros';"
echo ### ALL DATA IN REGISTROS ###
psql -h 127.0.0.1 -p 5432 -U postgres -d sis-ponto -P pager=off -c "SELECT * FROM ponto.registros;"
