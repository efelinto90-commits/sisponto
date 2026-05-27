# CONFIGURAÇÃO DO SERVIDOR LOCAWEB - OTIMIZAÇÕES

## 🔧 OTIMIZAÇÕES NO SERVIDOR

### 1. CONFIGURAR PHP.INI

Arquivo típico: `/etc/php/8.x/fpm/php.ini`

```ini
; Melhorar performance
max_execution_time = 60
max_input_time = 60
max_input_vars = 10000
memory_limit = 512M

; Desabilitar features desnecessárias
disable_functions = exec,passthru,shell_exec,system,proc_open,popen,curl_exec,curl_multi_exec,parse_ini_file,show_source
disable_classes = 

; Sessões
session.cache_limiter = nocache
session.gc_probability = 0
session.gc_divisor = 1
session.gc_maxlifetime = 86400

; Dados
post_max_size = 100M
upload_max_filesize = 100M

; OPcache (MUITO IMPORTANTE)
opcache.enable = 1
opcache.enable_cli = 1
opcache.memory_consumption = 256
opcache.interned_strings_buffer = 16
opcache.max_accelerated_files = 10000
opcache.revalidate_freq = 0
opcache.validate_timestamps = 1
opcache.fast_shutdown = 1

; PDO
pdo_cache_size = 128
```

### 2. CONFIGURAR POSTGRESQL.CONF

Arquivo: `/etc/postgresql/13/main/postgresql.conf`

```ini
# Conexões
max_connections = 100
superuser_reserved_connections = 3

# Memória
shared_buffers = 512MB  # 25% da RAM do servidor
effective_cache_size = 2GB  # 50-75% da RAM
maintenance_work_mem = 256MB
work_mem = 32MB  # 2-4MB por conexão esperada

# Query Planning
random_page_cost = 1.1  # Em SSD, colocar 1.1
effective_io_concurrency = 100  # SSD

# Logging
log_min_duration_statement = 1000  # Log queries > 1s
log_line_prefix = '%t [%p]: [%l-1] user=%u,db=%d,app=%a,client=%h '
log_checkpoints = on
log_connections = on
log_disconnections = on
log_lock_waits = on

# Autovacuum
autovacuum = on
autovacuum_naptime = 30s
autovacuum_vacuum_threshold = 50
autovacuum_analyze_threshold = 50
```

### 3. CONFIGURAR NGINX (se usar)

```nginx
# /etc/nginx/nginx.conf
user www-data;
worker_processes auto;
pid /run/nginx.pid;
worker_rlimit_nofile 65535;

events {
    worker_connections 10000;
    use epoll;
    multi_accept on;
}

http {
    # Gzip Compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1000;
    gzip_proxied any;
    gzip_types text/plain text/css text/xml text/javascript 
               application/x-javascript application/xml+rss 
               application/json application/javascript;
    gzip_disable "msie6";
    
    # Buffer sizes
    client_body_buffer_size 128k;
    client_max_body_size 100m;
    client_header_buffer_size 1k;
    large_client_header_buffers 4 16k;
    
    # Timeouts
    client_body_timeout 60;
    client_header_timeout 60;
    keepalive_timeout 65;
    send_timeout 60;
    
    # Cache
    proxy_cache_path /var/cache/nginx levels=1:2 keys_zone=my_cache:10m 
                     max_size=10g inactive=60m use_temp_path=off;
    
    # Server
    server {
        listen 80;
        server_name seu-dominio.com.br;
        
        root /var/www/sisponto/public;
        index index.php;
        
        # Cache estático
        location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
            expires 30d;
            add_header Cache-Control "public, immutable";
        }
        
        # PHP
        location ~ \.php$ {
            fastcgi_pass unix:/run/php/php8.1-fpm.sock;
            fastcgi_index index.php;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
            include fastcgi_params;
            
            # Cache
            fastcgi_cache my_cache;
            fastcgi_cache_valid 200 10m;
        }
        
        # Deny access to hidden files
        location ~ /\. {
            deny all;
        }
    }
}
```

### 4. CONFIGURAR PHP-FPM

Arquivo: `/etc/php/8.1/fpm/pool.d/www.conf`

```ini
[www]
user = www-data
group = www-data
listen = /run/php/php8.1-fpm.sock
listen.owner = www-data
listen.group = www-data

pm = dynamic
pm.max_children = 100
pm.start_servers = 10
pm.min_spare_servers = 5
pm.max_spare_servers = 20
pm.process_idle_timeout = 30s
pm.max_requests = 500

; Timeout
request_terminate_timeout = 60s

; Logs
catch_workers_output = yes
error_log = /var/log/php8.1-fpm-errors.log
slowlog = /var/log/php8.1-fpm-slow.log
slowlog_timeout = 5s
```

---

## 📊 MONITORAMENTO

### Verificar Performance

```bash
# CPU e Memória
top -b -n 1 | head -20

# Conexões PostgreSQL
psql -U postgres -c "SELECT count(*) as live_connections FROM pg_stat_activity;"

# Conexões PHP-FPM
pm2 status  # se usar PM2
systemctl status php8.1-fpm

# Espaço em disco
df -h

# I/O
iostat -x 1

# Rede
nethogs

# Conexões HTTP
netstat -an | grep :80 | wc -l
```

### Script de Monitoramento

```bash
#!/bin/bash
# /usr/local/bin/monitor-sisponto.sh

while true; do
    clear
    echo "=== SISPONTO MONITOR ==="
    echo "Data: $(date)"
    echo ""
    
    echo "=== PostgreSQL ==="
    psql -U postgres -c "SELECT count(*) as conexoes FROM pg_stat_activity;"
    psql -U postgres -c "SELECT * FROM pg_stat_statements LIMIT 5;"
    
    echo ""
    echo "=== Sistema ==="
    free -h | grep Mem
    df -h / | tail -1
    
    echo ""
    echo "=== PHP-FPM ==="
    systemctl status php8.1-fpm | grep Active
    
    sleep 30
done
```

---

## 🎯 CHECKLIST DE PRODUÇÃO

### Antes de Colocar em Produção

- [ ] Backup total do banco de dados
- [ ] Backup dos arquivos
- [ ] Testar em staging com dados reais
- [ ] Executar OPTIMIZATION_INDEXES.sql
- [ ] Configurar php.ini com valores recomendados
- [ ] Configurar postgresql.conf
- [ ] Ativar GZIP
- [ ] Testar load testing
- [ ] Configurar backups automáticos
- [ ] Configurar monitoramento
- [ ] Testar failover

### Depois de Colocar em Produção

- [ ] Monitorar por 24h
- [ ] Verificar logs de erro
- [ ] Testar fluxos principais
- [ ] Validar performance com ferramentas (LoadImpact, JMeter)
- [ ] Documentar tempos de resposta baseline
- [ ] Ajustar php.ini/postgres.conf conforme necessário

---

## 📉 TROUBLESHOOTING

### Sistema Lento Ainda Depois das Otimizações

1. Verificar queries lentas:
```sql
SELECT query, mean_exec_time FROM pg_stat_statements 
ORDER BY mean_exec_time DESC LIMIT 10;
```

2. Verificar conexões:
```bash
psql -U postgres -c "SELECT count(*) FROM pg_stat_activity;"
```

3. Verificar locks:
```sql
SELECT * FROM pg_locks WHERE granted = false;
```

4. Aumentar memory_limit (se necessário):
```php
// No início do arquivo problemático
ini_set('memory_limit', '1G');
```

### Erro "Too Many Connections"

1. Verificar pool size:
```bash
psql -U postgres -c "SHOW max_connections;"
```

2. Aumentar em postgresql.conf:
```ini
max_connections = 200
```

3. Reiniciar:
```bash
sudo systemctl restart postgresql
```

### Erro 502 Bad Gateway

1. Verificar PHP-FPM:
```bash
sudo systemctl status php8.1-fpm
sudo journalctl -u php8.1-fpm -n 50
```

2. Aumentar timeouts em nginx.conf:
```nginx
fastcgi_read_timeout 120s;
fastcgi_connect_timeout 120s;
```

---

## 💾 BACKUPS AUTOMÁTICOS

```bash
#!/bin/bash
# /usr/local/bin/backup-sisponto.sh

BACKUP_DIR="/backups/sisponto"
DATE=$(date +%Y%m%d_%H%M%S)

# Backup PostgreSQL
pg_dump -U postgres funad | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# Backup Arquivos
tar -czf $BACKUP_DIR/files_$DATE.tar.gz /var/www/sisponto

# Manter apenas 7 últimos backups
find $BACKUP_DIR -name "*.gz" -mtime +7 -delete

echo "Backup realizado em $DATE"
```

Agendar com cron:
```bash
0 3 * * * /usr/local/bin/backup-sisponto.sh >> /var/log/sisponto-backup.log 2>&1
```

---

Criado em: 2026-05-04
Versão: 1.0
