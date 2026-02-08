 Essence Art Gallery - E-Commerce Platform

A fully dockerized PHP-based e-commerce platform for an art gallery, featuring M-Pesa payment integration, user authentication, and a complete shopping cart system.

 Features

- **User Authentication** - Secure registration and login system
- **Product Catalog** - Browse artworks by category with detailed views
- **Shopping Cart** - Add items, manage quantities, and checkout
- **M-Pesa Integration** - Accept mobile payments (Kenyan market)
- **Order Management** - Track order status and payment history
- **Admin Dashboard** - Manage products, orders, and customers
- **Responsive Design** - Works on desktop and mobile devices

 Tech Stack

- **Frontend:** HTML, CSS, JavaScript
- **Backend:** PHP 8.1
- **Database:** MySQL 8.0
- **Server:** Apache (via Docker)
- **Containerization:** Docker & Docker Compose

 Prerequisites

Before you begin, ensure you have the following installed:

- [Docker](https://docs.docker.com/get-docker/) (version 20.10 or higher)
- [Docker Compose](https://docs.docker.com/compose/install/) (version 2.0 or higher)
- Git

 Docker Setup

This project uses Docker to containerize the entire application stack, making it easy to run anywhere without complex setup.

 Architecture

The application runs across 3 containers:

1. **web** - PHP 8.1 with Apache serving the application
2. **db** - MySQL 8.0 database
3. **phpmyadmin** - Database management interface

Quick Start

1. Clone the repository**
   ```bash
   git clone https://github.com/yourusername/essence-art-gallery.git
   cd essence-art-gallery
   ```

2. Start the containers**
   ```bash
   docker compose up -d
   ```

3. Access the application**
   - Website: http://localhost:8080
   - phpMyAdmin: http://localhost:8081
     - Server: `db`
     - Username: `root`
     - Password: `root`

4. Stop the containers**
   ```bash
   docker compose down
   ```

 Configuration

The `docker-compose.yml` file defines the entire stack:

```yaml
services:
  web:
    build: .
    ports:
      - "8080:80"
    volumes:
      - .:/var/www/html
    depends_on:
      - db

  db:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD: root
      MYSQL_DATABASE: essence_art_gallery
    ports:
      - "3307:3306"
    volumes:
      - db_data:/var/lib/mysql
      - ./database.sql:/docker-entrypoint-initdb.d/database.sql

  phpmyadmin:
    image: phpmyadmin
    ports:
      - "8081:80"
    environment:
      PMA_HOST: db
      PMA_USER: root
      PMA_PASSWORD: root
```

 Dockerfile

The custom PHP image includes necessary extensions:

```dockerfile
FROM php:8.1-apache

# Install MySQL extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Copy application files
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
```

 💾 Database

 Initial Setup

The database is automatically created and populated when you first run `docker compose up`. The `database.sql` file contains:

- Table schemas (Users, Products, Orders, Cart, etc.)
- Sample data for testing
- Proper foreign key constraints

 Database Connection

The application connects to the database using:

```php
$host = "db";  // Docker service name
$username = "root";
$password = "root";
$database = "essence_art_gallery";
```

 Manual Database Import

If you need to reimport the database:

```bash
docker compose exec db mysql -u root -proot essence_art_gallery < database.sql
```

 Development Workflow

 Making Code Changes

Thanks to Docker volumes, any changes to your PHP files are instantly reflected:

1. Edit files in your project directory
2. Refresh the browser - changes appear immediately
3. No need to rebuild containers for code changes

 Rebuilding After Dockerfile Changes

If you modify the `Dockerfile` or dependencies:

```bash
docker compose down
docker compose build
docker compose up -d
```

 Viewing Logs

```bash
# All services
docker compose logs

# Specific service
docker compose logs web
docker compose logs db

# Follow logs live
docker compose logs -f
```

 Accessing Container Shell

```bash
# Web container
docker compose exec web bash

# Database container
docker compose exec db bash
```

 📦 Project Structure

```
essence-art-gallery/
├── admin/              # Admin dashboard files
├── assets/             # CSS, JS, images
│   ├── css/
│   ├── js/
│   └── images/
├── uploads/            # User-uploaded files
│   └── artworks/
├── includes/           # PHP includes (config, functions)
├── index.php           # Homepage
├── products.php        # Product catalog
├── cart.php            # Shopping cart
├── checkout.php        # Checkout process
├── login.php           # User login
├── register.php        # User registration
├── database.sql        # Database schema and data
├── Dockerfile          # Docker image configuration
└── docker-compose.yml  # Multi-container setup
```
 🚦 Common Issues & Solutions

 Port Already in Use

If port 8080 or 3307 is already in use:

Option 1:** Stop the conflicting service
```bash
# For XAMPP MySQL
sudo systemctl stop mysql
```

Option 2:** Change ports in `docker-compose.yml`
```yaml
ports:
  - "9000:80"  # Change 8080 to 9000
```

 Database Connection Error

Make sure all containers are running:
```bash
docker compose ps
```

All should show "Up". If not:
```bash
docker compose down -v
docker compose up -d
```

 Containers Not Starting

Check logs for errors:
```bash
docker compose logs db
docker compose logs web
```

 Fresh Start

To completely reset everything:
```bash
docker compose down -v  # -v removes volumes
docker compose up -d
```

## 🔐 Security Notes

For Production Deployment:**

1. Change default passwords in `docker-compose.yml`
2. Use environment variables for sensitive data
3. Enable HTTPS with SSL certificates
4. Restrict database access
5. Update container images regularly

Example with environment variables:

```yaml
db:
  environment:
    MYSQL_ROOT_PASSWORD: ${DB_PASSWORD}
    MYSQL_DATABASE: ${DB_NAME}
```

Create `.env` file:
```
DB_PASSWORD=your_secure_password
DB_NAME=essence_art_gallery
```

 Deployment

 To Production Server

1. Install Docker on your server**
   ```bash
   curl -fsSL https://get.docker.com -o get-docker.sh
   sudo sh get-docker.sh
   ```

2. Clone your repository**
   ```bash
   git clone https://github.com/yourusername/essence-art-gallery.git
   cd essence-art-gallery
   ```

3. Update production settings**
   - Change database passwords
   - Update domain configuration
   - Set up SSL certificates

4. Run containers**
   ```bash
   docker compose up -d
   ```

### With CI/CD (GitHub Actions Example)

```yaml
name: Deploy

on:
  push:
    branches: [ main ]

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Deploy to server
        run: |
          ssh user@yourserver.com 'cd /path/to/project && git pull && docker compose up -d --build'
```

 Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

 Author

Igariura Muraguri**
- GitHub: [@Igariura](https://github.com/Igariura)

 Acknowledgments

- Docker documentation and community
- PHP and MySQL communities
- M-Pesa Daraja API documentation

Contact

For questions or support, please open an issue or contact [igariuramuraguri@gmail.com]

---

**Built with ❤️ and Docker**
