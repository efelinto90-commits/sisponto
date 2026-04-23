@echo off
set PGPASSWORD=master10
psql -h 127.0.0.1 -p 5432 -U postgres -d postgres -c "SELECT pid, state, query FROM pg_stat_activity"
