@echo off
set PGPASSWORD=master10
psql -h 127.0.0.1 -p 5432 -U postgres -d sis-ponto -f "c:\xampp\htdocs\sisponto\fix_funcionarios_table.sql"
