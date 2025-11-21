# 🚀 Guia de Deploy - VesteZap em VPS (Sem Docker)

Este guia detalha a instalação completa do VesteZap em uma VPS Ubuntu/Debian sem Docker, incluindo todas as dependências, configurações e SSL gratuito.

---

## 📋 Pré-requisitos

- VPS com Ubuntu 22.04 LTS ou Debian 12
- Acesso root ou usuário com sudo
- Domínio apontado para o IP da VPS (vestezap.com.br)
- Mínimo 2GB RAM recomendado
- Mínimo 20GB de armazenamento

---

## 🔧 Passo 1: Instalação do Nala e Atualização do Sistema

```bash
# Instalar Nala (gerenciador de pacotes melhorado)
sudo apt update
sudo apt install -y nala

# Atualizar lista de pacotes com Nala
sudo nala update && sudo nala upgrade -y

# Instalar ferramentas básicas
sudo nala install -y curl wget git unzip software-properties-common
```

---

## 🗄️ Passo 2: Instalação do MySQL 8.0

### Opção A: Instalar MySQL via repositório oficial (Recomendado)

```bash
# Baixar e instalar o pacote de configuração do MySQL
cd /tmp
wget https://dev.mysql.com/get/mysql-apt-config_0.8.29-1_all.deb
sudo nala install -y ./mysql-apt-config_0.8.29-1_all.deb

# Durante a instalação, selecione:
# - MySQL Server & Cluster (selecionado)
# - mysql-8.0 (ou a versão desejada)
# - OK

# Atualizar lista de pacotes
sudo nala update

# Instalar MySQL Server
sudo nala install -y mysql-server

# Iniciar e habilitar MySQL
sudo systemctl start mysql
sudo systemctl enable mysql

# Configurar segurança do MySQL
sudo mysql_secure_installation

# Durante a configuração, você será perguntado:
# - Definir senha do root (anote essa senha!)
# - Remover usuários anônimos? (Y)
# - Desabilitar login remoto do root? (Y)
# - Remover banco de teste? (Y)
# - Recarregar privilégios? (Y)
```

### Opção B: Usar MariaDB (Alternativa compatível)

Se preferir usar MariaDB (compatível com MySQL):

```bash
# Instalar MariaDB Server
sudo nala install -y mariadb-server

# Iniciar e habilitar MariaDB
sudo systemctl start mariadb
sudo systemctl enable mariadb

# Configurar segurança do MariaDB
csudo mysql_secure_installation

# Durante a configuração, você será perguntado:
# - Definir senha do root (anote essa senha!)
# - Remover usuários anônimos? (Y)
# - Desabilitar login remoto do root? (Y)
# - Remover banco de teste? (Y)
# - Recarregar privilégios? (Y)
```

**Nota:** Se usar MariaDB, os comandos `mysql` funcionam da mesma forma. Apenas certifique-se de que o serviço está rodando com `sudo systemctl status mariadb`.

### Criar banco de dados e usuário

```bash
# Acessar MySQL/MariaDB como root
sudo mysql -u root -p
# ou, se o método de autenticação for diferente:
# sudo mysql -u root

# No prompt do MySQL/MariaDB, execute:
CREATE DATABASE vestezap_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'vestezap_user'@'localhost' IDENTIFIED BY 'SUA_SENHA_SEGURA_AQUI';
GRANT ALL PRIVILEGES ON vestezap_db.* TO 'vestezap_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

**⚠️ IMPORTANTE:** 
- Substitua `SUA_SENHA_SEGURA_AQUI` por uma senha forte e anote-a!
- Se você instalou MariaDB, o comando `mysql` funciona da mesma forma
- Se tiver problemas de autenticação, tente `sudo mysql` sem senha primeiro

---

## 🐘 Passo 3: Instalação do PHP 8.2+

```bash
# Adicionar repositório do PHP
sudo add-apt-repository ppa:ondrej/php -y
sudo nala update

# Instalar PHP 8.2 e extensões necessárias
sudo nala install -y php8.2-fpm php8.2-cli php8.2-common php8.2-mysql \
    php8.2-zip php8.2-gd php8.2-mbstring php8.2-curl php8.2-xml \
    php8.2-bcmath php8.2-intl php8.2-readline

# Verificar instalação
php -v
```

### Configurar PHP-FPM

```bash
# Editar configuração do PHP-FPM
sudo nano /etc/php/8.2/fpm/php.ini
```

Altere as seguintes linhas:
```ini
upload_max_filesize = 20M
post_max_size = 20M
memory_limit = 256M
max_execution_time = 300
```

```bash
# Reiniciar PHP-FPM
sudo systemctl restart php8.2-fpm
sudo systemctl enable php8.2-fpm
```

---

## 📦 Passo 4: Instalação do Composer

```bash
# Baixar e instalar Composer
cd ~
curl -sS https://getcomposer.org/installer | php

# Mover para diretório global
sudo mv composer.phar /usr/local/bin/composer

# Dar permissão de execução
sudo chmod +x /usr/local/bin/composer

# Verificar instalação
composer --version
```

---

## 📦 Passo 5: Instalação do Node.js 20.x

```bash
# Instalar Node.js via NodeSource
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo nala install -y nodejs

# Verificar instalação
node -v
npm -v

# Instalar build tools (necessário para algumas dependências)
sudo nala install -y build-essential
```

---

## 🌐 Passo 6: Instalação do Nginx

```bash
# Instalar Nginx
sudo nala install -y nginx

# Iniciar e habilitar Nginx
sudo systemctl start nginx
sudo systemctl enable nginx

# Verificar status
sudo systemctl status nginx
```

---

## 📥 Passo 7: Clonar e Configurar o Projeto

```bash
# Criar diretório para aplicações
sudo mkdir -p /var/www
cd /var/www

# Clonar repositório
sudo git clone git@bitbucket.org:codigo101/vestezap.git
cd vestezap

# Dar permissões corretas
sudo chown -R www-data:www-data /var/www/vestezap
sudo chmod -R 755 /var/www/vestezap
```

**⚠️ NOTA:** Se você estiver usando SSH para clonar, certifique-se de que as chaves SSH estão configuradas no servidor. Alternativamente, você pode usar HTTPS:

```bash
# Alternativa com HTTPS (será solicitada autenticação)
sudo git clone https://bitbucket.org/codigo101/vestezap.git
```

---

## ⚙️ Passo 8: Configuração do Backend (Laravel)

```bash
cd /var/www/vestezap/backend

# Instalar dependências do Composer
sudo -u www-data composer install --no-dev --optimize-autoloader

# Copiar arquivo de ambiente
sudo -u www-data cp .env.example .env

# Gerar chave da aplicação
sudo -u www-data php artisan key:generate

# Configurar permissões de storage
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Criar link simbólico para storage público
sudo -u www-data php artisan storage:link
```

### Configurar arquivo .env

```bash
# Editar arquivo .env
sudo nano /var/www/vestezap/backend/.env
```

Configure as seguintes variáveis (substitua pelos seus valores):

```env
APP_NAME="VesteZap"
APP_ENV=production
APP_KEY=base64:GERADO_PELO_ARTISAN_KEY_GENERATE
APP_DEBUG=false
APP_URL=https://vestezap.com.br

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=vestezap_db
DB_USERNAME=vestezap_user
DB_PASSWORD=SUA_SENHA_DO_BANCO_AQUI

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MAIL_MAILER=smtp
MAIL_HOST=seu-servidor-smtp.com
MAIL_PORT=587
MAIL_USERNAME=seu-email@vestezap.com.br
MAIL_PASSWORD=SUA_SENHA_EMAIL_AQUI
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@vestezap.com.br"
MAIL_FROM_NAME="${APP_NAME}"

FRONTEND_URL=https://vestezap.com.br

# reCAPTCHA v3 - Secret Key (obter em https://www.google.com/recaptcha/admin)
RECAPTCHA_SECRET_KEY=SUA_CHAVE_SECRET_KEY_AQUI
```

**⚠️ IMPORTANTE:**
- Substitua `SUA_SENHA_DO_BANCO_AQUI` pela senha do banco criada anteriormente
- Configure um serviço de email (Gmail, SendGrid, Mailgun, etc.)
- **Configurar reCAPTCHA v3:**
  1. Acesse: https://www.google.com/recaptcha/admin/create
  2. Selecione "reCAPTCHA v3"
  3. Adicione os domínios: `vestezap.com.br` e `api.vestezap.com.br`
  4. Copie a **Site Key** → use no frontend (`.env.production`)
  5. Copie a **Secret Key** → use no backend (`.env` acima)

### Executar migrations

```bash
# Executar migrations
sudo -u www-data php artisan migrate --force

# (Opcional) Executar seeders para dados iniciais
sudo -u www-data php artisan db:seed
```

### Otimizar Laravel para produção

```bash
# Cache de configuração
sudo -u www-data php artisan config:cache

# Cache de rotas
sudo -u www-data php artisan route:cache

# Cache de views
sudo -u www-data php artisan view:cache

# Otimizar autoloader
sudo -u www-data composer install --optimize-autoloader --no-dev
```

---

## 🎨 Passo 9: Build do Frontend

```bash
cd /var/www/vestezap/frontend

# Instalar dependências
sudo npm install

# Configurar variáveis de ambiente
sudo nano .env.production
```

Crie o arquivo `.env.production` com:

```env
# URL da API (subdomínio api.vestezap.com.br)
VITE_API_BASE_URL=https://api.vestezap.com.br/api

# Chave Site Key do reCAPTCHA v3 (obter em https://www.google.com/recaptcha/admin/create)
VITE_RECAPTCHA_SITE_KEY=sua_chave_site_key_aqui
```

**⚠️ IMPORTANTE - Configurar reCAPTCHA v3:**

1. Acesse: https://www.google.com/recaptcha/admin/create
2. Selecione "reCAPTCHA v3"
3. Adicione o domínio: `vestezap.com.br`
4. Copie a **Site Key** e cole em `VITE_RECAPTCHA_SITE_KEY`
5. Copie a **Secret Key** e configure no backend (ver seção Backend)

**⚠️ NOTA:** Se você usa subdomínio separado para API (`api.vestezap.com.br`), certifique-se de adicionar ambos os domínios no reCAPTCHA:
- `vestezap.com.br`
- `api.vestezap.com.br` (se necessário)

```bash
# Build para produção
sudo npm run build

# Dar permissões corretas
sudo chown -R www-data:www-data /var/www/vestezap/frontend/dist
```

---

## 🔧 Passo 10: Configuração do Nginx

### Configurar site para o backend (API)

```bash
sudo nano /etc/nginx/sites-available/vestezap-api
```

Adicione a seguinte configuração:

```nginx
server {
    listen 80;
    server_name api.vestezap.com.br;
    root /var/www/vestezap/backend/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### Configurar site para o frontend

```bash
sudo nano /etc/nginx/sites-available/vestezap-frontend
```

Adicione a seguinte configuração:

```nginx
server {
    listen 80;
    server_name vestezap.com.br www.vestezap.com.br;
    root /var/www/vestezap/frontend/dist;

    index index.html;

    charset utf-8;

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript application/x-javascript application/xml+rss application/javascript application/json;

    # Cache static assets
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # SPA routing - todas as rotas vão para index.html
    location / {
        try_files $uri $uri/ /index.html;
    }

    # API proxy (opcional - se quiser servir API no mesmo domínio)
    location /api {
        proxy_pass http://127.0.0.1:8000;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_cache_bypass $http_upgrade;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

### Habilitar sites

```bash
# Criar links simbólicos
sudo ln -s /etc/nginx/sites-available/vestezap-api /etc/nginx/sites-enabled/
sudo ln -s /etc/nginx/sites-available/vestezap-frontend /etc/nginx/sites-enabled/

# Remover site padrão (opcional)
sudo rm /etc/nginx/sites-enabled/default

# Testar configuração
sudo nginx -t

# Se tudo estiver OK, recarregar Nginx
sudo systemctl reload nginx
```

---

## 🔒 Passo 11: Configurar SSL com Let's Encrypt (Certbot)

```bash
# Instalar Certbot
sudo nala install -y certbot python3-certbot-nginx

# Obter certificado SSL para o domínio principal
sudo certbot --nginx -d vestezap.com.br -d www.vestezap.com.br

# Obter certificado SSL para subdomínio da API (se usar)
sudo certbot --nginx -d api.vestezap.com.br

# Durante a configuração:
# - Digite seu email
# - Aceite os termos
# - Escolha se quer redirecionar HTTP para HTTPS (recomendado: 2)
```

### Renovação automática

O Certbot já configura renovação automática, mas você pode testar:

```bash
# Testar renovação
sudo certbot renew --dry-run

# Ver certificados instalados
sudo certbot certificates
```

---

## 🔄 Passo 12: Configurar Process Manager (Opcional - para produção)

Para garantir que o Laravel sempre esteja rodando, você pode usar Supervisor ou systemd. Vamos usar systemd:

```bash
sudo nano /etc/systemd/system/vestezap-api.service
```

Adicione:

```ini
[Unit]
Description=VesteZap API Service
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/var/www/vestezap/backend
ExecStart=/usr/bin/php artisan serve --host=127.0.0.1 --port=8000
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target
```

```bash
# Habilitar e iniciar serviço
sudo systemctl daemon-reload
sudo systemctl enable vestezap-api
sudo systemctl start vestezap-api

# Verificar status
sudo systemctl status vestezap-api
```

**Nota:** Se você configurou o Nginx para servir diretamente via PHP-FPM (recomendado), não precisa deste serviço. O serviço acima é apenas se quiser usar `php artisan serve`.

---

## 🔥 Passo 13: Configurar Firewall

```bash
# Instalar UFW (se não estiver instalado)
sudo nala install -y ufw

# Permitir SSH (IMPORTANTE - faça antes de habilitar!)
sudo ufw allow OpenSSH

# Permitir HTTP e HTTPS
sudo ufw allow 'Nginx Full'

# Habilitar firewall
sudo ufw enable

# Verificar status
sudo ufw status
```

---

## ✅ Passo 14: Verificação Final

### Verificar serviços

```bash
# Verificar status de todos os serviços
sudo systemctl status nginx
sudo systemctl status php8.2-fpm
sudo systemctl status mysql
# ou, se instalou MariaDB:
# sudo systemctl status mariadb
```

### Testar aplicação

1. Acesse `https://vestezap.com.br` no navegador
2. Acesse `https://vestezap.com.br/admin/login` para o painel admin
3. Verifique se o SSL está funcionando (cadeado verde)

### Verificar logs

```bash
# Logs do Nginx
sudo tail -f /var/log/nginx/error.log
sudo tail -f /var/log/nginx/access.log

# Logs do Laravel
sudo tail -f /var/www/vestezap/backend/storage/logs/laravel.log

# Logs do PHP-FPM
sudo tail -f /var/log/php8.2-fpm.log
```

---

## 🔄 Comandos de Manutenção

### Atualizar aplicação

```bash
cd /var/www/vestezap

# Atualizar código
sudo git pull origin main

# Backend
cd backend
sudo -u www-data composer install --no-dev --optimize-autoloader
sudo -u www-data php artisan migrate --force
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache

# Frontend
cd ../frontend
sudo npm install
sudo npm run build

# Recarregar serviços
sudo systemctl reload nginx
sudo systemctl reload php8.2-fpm
```

### Limpar cache

```bash
cd /var/www/vestezap/backend

# Limpar todos os caches
sudo -u www-data php artisan cache:clear
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan route:clear
sudo -u www-data php artisan view:clear

# Recriar caches
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
```

### Backup do banco de dados

```bash
# Criar backup
sudo mysqldump -u vestezap_user -p vestezap_db > backup_$(date +%Y%m%d_%H%M%S).sql

# Restaurar backup
sudo mysql -u vestezap_user -p vestezap_db < backup_YYYYMMDD_HHMMSS.sql
```

### Monitorar recursos

```bash
# Uso de memória e CPU
htop

# Espaço em disco
df -h

# Processos do PHP
ps aux | grep php

# Conexões do MySQL/MariaDB
sudo mysqladmin -u root -p processlist
```

---

## 🐛 Troubleshooting

### Erro 502 Bad Gateway

```bash
# Verificar se PHP-FPM está rodando
sudo systemctl status php8.2-fpm

# Reiniciar PHP-FPM
sudo systemctl restart php8.2-fpm

# Verificar logs
sudo tail -f /var/log/nginx/error.log
```

### Erro de permissões

```bash
# Corrigir permissões
sudo chown -R www-data:www-data /var/www/vestezap
sudo chmod -R 755 /var/www/vestezap
sudo chmod -R 775 /var/www/vestezap/backend/storage
sudo chmod -R 775 /var/www/vestezap/backend/bootstrap/cache
```

### Erro de conexão com banco

```bash
# Verificar se MySQL/MariaDB está rodando
sudo systemctl status mysql
# ou, se instalou MariaDB:
# sudo systemctl status mariadb

# Testar conexão
sudo mysql -u vestezap_user -p vestezap_db

# Verificar configuração no .env
sudo nano /var/www/vestezap/backend/.env
```

### SSL não funciona

```bash
# Verificar certificado
sudo certbot certificates

# Renovar certificado manualmente
sudo certbot renew

# Verificar configuração do Nginx
sudo nginx -t
```

---

## 📝 Checklist de Deploy

- [ ] Sistema atualizado
- [ ] MySQL instalado e configurado
- [ ] PHP 8.2+ instalado com extensões
- [ ] Composer instalado
- [ ] Node.js 20.x instalado
- [ ] Nginx instalado e configurado
- [ ] Projeto clonado
- [ ] Backend configurado (.env)
- [ ] Migrations executadas
- [ ] Frontend buildado
- [ ] SSL configurado (Let's Encrypt)
- [ ] Firewall configurado
- [ ] Serviços rodando
- [ ] Aplicação acessível via HTTPS
- [ ] Logs verificados

---

## 🔐 Segurança Adicional (Recomendado)

### Desabilitar login root via SSH

```bash
sudo nano /etc/ssh/sshd_config
```

Altere:
```
PermitRootLogin no
PasswordAuthentication no  # Se usar chaves SSH
```

```bash
sudo systemctl restart sshd
```

### Configurar fail2ban

```bash
sudo nala install -y fail2ban
sudo systemctl enable fail2ban
sudo systemctl start fail2ban
```

---

## 📞 Suporte

Em caso de problemas:
1. Verifique os logs mencionados acima
2. Verifique o status dos serviços
3. Verifique as permissões de arquivos
4. Verifique a configuração do .env

---

**🎉 Parabéns! Seu VesteZap está no ar!**

Acesse: `https://vestezap.com.br`

