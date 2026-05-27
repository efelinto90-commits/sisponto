# Configuração de Proxy Reverso (Apache XAMPP)

Este documento descreve como configurar o Apache do XAMPP para atuar como um Proxy Reverso para o serviço de Reconhecimento Facial (DeepFace FastAPI).

## 1. Alterações Realizadas no Servidor Local

### Apache (`httpd.conf`)
- Habilitado o módulo `mod_proxy_http`.
- Adicionada regra de redirecionamento na porta 80:
  ```apache
  ProxyPass /api_facial/ http://127.0.0.1:5000/
  ProxyPassReverse /api_facial/ http://127.0.0.1:5000/
  ProxyPreserveHost On
  ```

### Apache SSL (`httpd-ssl.conf`)
- Adicionada regra de redirecionamento na porta 443 (HTTPS):
  ```apache
  ProxyPass /api_facial/ http://127.0.0.1:5000/
  ProxyPassReverse /api_facial/ http://127.0.0.1:5000/
  ProxyPreserveHost On
  ```

### API Python (`deepface_service.py`)
- Habilitado suporte a **CORS** para permitir que o site hospedado na Locaweb consiga realizar chamadas para este servidor local.

---

## 2. Instruções de Implementação (Locaweb)

Para colocar o sistema em produção com a Locaweb e o seu servidor local, siga estes passos:

### Passo 1: Reiniciar o Servidor
1. Abra o **XAMPP Control Panel**.
2. Clique em **Stop** no Apache.
3. Clique em **Start** no Apache.

### Passo 2: Configuração de Rede
1. No seu roteador, faça o redirecionamento das portas **80** e **443** para o IP local deste computador.
2. Certifique-se de que o firewall do Windows permite conexões nessas portas.

### Passo 3: Atualizar o arquivo `ponto.php`
No servidor da **Locaweb**, você deve editar o arquivo `api/ponto.php` (linha 26) para apontar para o seu endereço público:

```php
// Altere de:
// $url = "http://127.0.0.1:5000/" . $endpoint;

// Para (Exemplo com IP ou Domínio):
$url = "https://seu-ip-ou-dominio.com/api_facial/" . $endpoint;
```

> **IMPORTANTE**: O uso de HTTPS é obrigatório para que a câmera e o GPS funcionem nos navegadores modernos.

---

## 3. Comandos Úteis
- **Verificar se a API está rodando:** `curl http://127.0.0.1:5000/`
- **Verificar o Proxy (Local):** `curl http://127.0.0.1/api_facial/`
