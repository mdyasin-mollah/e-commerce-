# Installation

1. PHP 8.2+ with PDO MySQL and MySQL 8+ required.
2. Copy `.env.example` to `.env`, set database credentials and APP_URL.
3. Import `database/schema.sql`.
4. Configure the web root to `public/`. Local test: `php -S 127.0.0.1:8000 -t public`.
5. Create the first administrator from project root (change email/password first):

```bash
php -r 'require "app/core.php"; $s=db()->prepare("INSERT INTO users(name,email,password,role) VALUES(?,?,?,?)"); $s->execute(["Store Admin","admin@example.com",password_hash("CHANGE_ME_NOW",PASSWORD_DEFAULT),"admin"]);'
```

Before production: enforce HTTPS, set APP_DEBUG=false, protect .env, add rate limiting, validated image uploads, email and shipping providers, privacy/return policies, backups and tests. Checkout currently supports cash on delivery only; integrate a compliant payment provider server-side. Never store card numbers or CVV.
