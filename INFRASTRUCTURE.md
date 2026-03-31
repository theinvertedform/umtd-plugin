# UMT Studio — Infrastructure

Last updated: 2026-03-31

---

## Server

| Property | Value |
|---|---|
| Provider | AWS EC2, ca-central-1 |
| OS | Ubuntu 24.04 LTS |
| Instance ID | i-0e4757a759e2a9991 |
| Elastic IP | 52.60.213.8 |
| Access | AWS SSM Session Manager — port 22 not open |

```bash
aws ssm start-session --target i-0e4757a759e2a9991
```

PPAs: `ppa:ondrej/php` (PHP 8.4), `ppa:ondrej/nginx` (nginx 1.28.x).

---

## Services

All services run natively on the EC2 instance.

| Service | Runtime | Port | Unit |
|---|---|---|---|
| nginx | apt | 80, 443 | `nginx.service` |
| PHP-FPM 8.4 | apt | unix socket | `php8.4-fpm.service` |
| MariaDB | apt | 127.0.0.1:3306 | `mariadb.service` |
| PostgreSQL 16 | apt | 127.0.0.1:5432 | `postgresql@16-main.service` |
| listmonk | binary | 127.0.0.1:9000 | `listmonk.service` |
| certbot | apt + timer | — | `certbot.timer` |

**PostgreSQL:** use `postgresql@16-main.service` — `postgresql.service` is a meta-unit that exits immediately.

### WordPress

One WordPress install per client at `/var/www/{client}/htdocs/`. Each install runs the base theme and base plugin, extended by a client-specific child plugin and child theme.

```
/var/www/{client}/htdocs/
    wp-config.php                            — not in git
    wp-content/themes/umt-design/           — base theme, git repo
    wp-content/themes/umt-design-{client}/  — child theme, git repo
    wp-content/plugins/umt-studio/          — base plugin, git repo
    wp-content/plugins/umt-studio-{client}/ — child plugin, git repo
```

ACF is installed via WP admin and is not tracked in git. PHP-FPM socket: `/run/php/php8.4-fpm.sock`.

### listmonk

Version: v6.0.0 — Binary: `/usr/local/bin/listmonk` — Config: `/etc/listmonk/config.toml`
Database: PostgreSQL, db `listmonk`, user `listmonk`
SMTP via AWS SES (ca-central-1). Credentials injected via `EnvironmentFile=/etc/listmonk/secrets.env`.

---

## Secrets Management

Secrets are stored in `/etc/[service]/secrets.env`, injected via `EnvironmentFile` in systemd units. Files are `chmod 600`, owned by root, not in git. WordPress DB credentials live in `wp-config.php` per standard WordPress convention.

---

## File Transfer

Bidirectional transfer between local and EC2 via S3 bucket `umt-temp-transfer` (ca-central-1). Always delete objects from the bucket after transfer.

```bash
# local → EC2
aws s3 cp ~/file.sql s3://umt-temp-transfer/file.sql
# EC2
aws s3 cp s3://umt-temp-transfer/file.sql /home/ubuntu/file.sql
aws s3 rm s3://umt-temp-transfer/file.sql
```

---

## nginx

Config root: `/etc/nginx/`. Default site disabled. All server blocks serve `/.well-known/acme-challenge/` from `/var/www/certbot/`.

| Domain | Backend |
|---|---|
| news.umt.world | http://127.0.0.1:9000 (listmonk), Cloudflare-only |
| {clientdomain.com} | unix:/run/php/php8.4-fpm.sock (WordPress) |

---

## SSL & Certbot

Webroot authenticator (`/var/www/certbot`). Timer runs twice daily. One cert per domain. `www.news.umt.world` is a SAN on the `news.umt.world` cert — the Cloudflare CNAME must remain active or renewal fails.

```bash
sudo certbot renew --dry-run
```

---

## CI/CD & GitHub Actions

```
git push → GitHub Actions → AWS OIDC → IAM role ec2-github → SSM SendCommand → /usr/local/bin/deploy
```

IAM role: `arn:aws:iam::828007040661:role/ec2-github`
Trust policy: `repo:theinvertedform/*:ref:refs/heads/main` — covers all repos under theinvertedform.

### Deploy Script

`/usr/local/bin/deploy` — generic, takes target path as argument, chmod 755, root-owned.

```bash
#!/bin/bash
set -e
TARGET="$1"
if [ -z "$TARGET" ]; then echo "Usage: deploy <target-path>" >&2; exit 1; fi
export HOME=/home/ubuntu
export GIT_SSH_COMMAND="ssh -i /home/ubuntu/.ssh/github_deploy -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null"
git config --global --add safe.directory "$TARGET"
cd "$TARGET"
git pull
chown -R www-data:www-data "$TARGET"
chown -R ubuntu:ubuntu "$TARGET/.git"
```

### Deploy Architecture

Base repos (`umt-studio`, `umt-design`) carry no deploy workflow. The child plugin repo owns the deploy workflow and is responsible for deploying both the base plugin and itself in sequence — base first, child second. A base plugin failure aborts before the child deploys. The child theme repo follows the same pattern for the theme pair.

The workflow template lives at `.github/workflows/deploy.yml` in `umt-studio-child`. Copy it into each per-client child repo and substitute `{client}`.

```yaml
name: Deploy
on:
  push:
    branches: [main]
jobs:
  deploy:
    runs-on: ubuntu-latest
    permissions:
      id-token: write
      contents: read
    steps:
      - uses: aws-actions/configure-aws-credentials@v4
        with:
          role-to-assume: arn:aws:iam::828007040661:role/ec2-github
          aws-region: ca-central-1

      - name: Deploy base plugin
        run: |
          COMMAND_ID=$(aws ssm send-command \
            --instance-ids i-0e4757a759e2a9991 \
            --document-name AWS-RunShellScript \
            --parameters 'commands=["/usr/local/bin/deploy /var/www/{client}/htdocs/wp-content/plugins/umt-studio"]' \
            --query Command.CommandId --output text)
          aws ssm wait command-executed --command-id $COMMAND_ID --instance-id i-0e4757a759e2a9991
          aws ssm get-command-invocation --command-id $COMMAND_ID --instance-id i-0e4757a759e2a9991 \
            --query '[StatusDetails,StandardOutputContent,StandardErrorContent]' --output text

      - name: Deploy child plugin
        run: |
          COMMAND_ID=$(aws ssm send-command \
            --instance-ids i-0e4757a759e2a9991 \
            --document-name AWS-RunShellScript \
            --parameters 'commands=["/usr/local/bin/deploy /var/www/{client}/htdocs/wp-content/plugins/umt-studio-{client}"]' \
            --query Command.CommandId --output text)
          aws ssm wait command-executed --command-id $COMMAND_ID --instance-id i-0e4757a759e2a9991
          aws ssm get-command-invocation --command-id $COMMAND_ID --instance-id i-0e4757a759e2a9991 \
            --query '[StatusDetails,StandardOutputContent,StandardErrorContent]' --output text
```

SSM `--parameters` must be a single line. Always use a deploy script; never inline commands in `--parameters`.

### Repos

| Repo | Role | Deploy workflow |
|---|---|---|
| `theinvertedform/umt-studio` | Base plugin | None — deployed by child plugin repo |
| `theinvertedform/umt-design` | Base theme | None — deployed by child theme repo |
| `theinvertedform/umt-studio-child` | Child plugin template | Deploys base plugin then itself on push to main |
| `theinvertedform/listmonk` | listmonk static assets | `deploy /var/www/listmonk` |

`umt-studio-child` is the template. Per-client child repos are instantiated from it — they follow the same deploy pattern with `{client}` substituted and are not listed here.

---

## Client Deploy Process

**1. DNS** — Add A record in Cloudflare → `52.60.213.8`.

**2. MariaDB**
```bash
sudo mysql -u root -p << 'EOF'
CREATE DATABASE {client};
CREATE USER '{client}'@'localhost' IDENTIFIED BY 'GENERATED_PASSWORD';
GRANT ALL PRIVILEGES ON {client}.* TO '{client}'@'localhost';
FLUSH PRIVILEGES;
EOF
```

**3. WordPress**
```bash
cd /tmp && wget https://wordpress.org/latest.tar.gz && tar -xzf latest.tar.gz
sudo mkdir -p /var/www/{client}/htdocs
sudo cp -a wordpress/. /var/www/{client}/htdocs/
sudo chown -R www-data:www-data /var/www/{client}/htdocs
sudo cp /var/www/{client}/htdocs/wp-config-sample.php /var/www/{client}/htdocs/wp-config.php
sudo vim /var/www/{client}/htdocs/wp-config.php
# Set DB_NAME, DB_USER, DB_PASSWORD, DB_HOST=localhost
# Replace secret keys: https://api.wordpress.org/secret-key/1.1/salt/
```

**4. Clone repos**
```bash
sudo chown ubuntu:ubuntu /var/www/{client}/htdocs/wp-content/themes
sudo chown ubuntu:ubuntu /var/www/{client}/htdocs/wp-content/plugins
cd /var/www/{client}/htdocs/wp-content/themes
git clone git@github.com:theinvertedform/umt-design.git
cd /var/www/{client}/htdocs/wp-content/plugins
git clone git@github.com:theinvertedform/umt-studio.git
git clone git@github.com:theinvertedform/umt-studio-{client}.git
sudo chown -R www-data:www-data /var/www/{client}/htdocs/wp-content
sudo chown -R ubuntu:ubuntu /var/www/{client}/htdocs/wp-content/themes/umt-design/.git
sudo chown -R ubuntu:ubuntu /var/www/{client}/htdocs/wp-content/plugins/umt-studio/.git
sudo chown -R ubuntu:ubuntu /var/www/{client}/htdocs/wp-content/plugins/umt-studio-{client}/.git
```

Subsequent deploys are handled automatically by the child repo's GitHub Actions workflow on push to main.

**5. Database import (migration only)**
```bash
aws s3 cp ~/client.sql s3://umt-temp-transfer/client.sql
# on EC2:
aws s3 cp s3://umt-temp-transfer/client.sql /home/ubuntu/client.sql
mysql -u {client} -p {client} < /home/ubuntu/client.sql
mysql -u {client} -p {client} -e "UPDATE wp_options SET option_value='https://{clientdomain.com}' WHERE option_name IN ('siteurl', 'home');"
aws s3 rm s3://umt-temp-transfer/client.sql
```

**6. nginx server block**
```bash
sudo tee /etc/nginx/sites-available/{clientdomain.com} << 'EOF'
server {
    listen 80;
    server_name {clientdomain.com};
    server_tokens off;
    root /var/www/{client}/htdocs;
    index index.php;
    location /.well-known/acme-challenge/ { root /var/www/certbot; }
    location / { return 301 https://$host$request_uri; }
}
server {
    listen 443 ssl;
    server_name {clientdomain.com};
    server_tokens off;
    root /var/www/{client}/htdocs;
    index index.php;
    ssl_certificate /etc/letsencrypt/live/{clientdomain.com}/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/{clientdomain.com}/privkey.pem;
    include /etc/letsencrypt/options-ssl-nginx.conf;
    ssl_dhparam /etc/letsencrypt/ssl-dhparams.pem;
    location / { try_files $uri $uri/ /index.php?$args; }
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.4-fpm.sock;
    }
    location ~ /\.ht { deny all; }
}
EOF
sudo ln -s /etc/nginx/sites-available/{clientdomain.com} /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
```

**7. SSL**
```bash
sudo certbot --nginx -d {clientdomain.com}
```

**8. CI/CD** — Copy `.github/workflows/deploy.yml` from `umt-studio-child` into the client child repo. Substitute `{client}`. Push to main to verify.
