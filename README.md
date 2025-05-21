# League of Legends E-Commerce Platform

A comprehensive e-commerce platform built with WordPress, specializing in League of Legends merchandise, in-game items, and exclusive accessories. This project was my first venture after finishing high school, and it represents the starting point of my journey into web development and e-commerce.

Check out our product demo: [RiotPO Demo Video](https://youtu.be/36pmUOyMXk0)

[![Riotpo Website Preview](https://github-production-user-asset-6210df.s3.amazonaws.com/52969662/281577732-a99acf93-cdaa-41ba-92ea-675b43a23f37.png)](https://youtu.be/36pmUOyMXk0)

## Project Overview

Riotpo is an online store dedicated to League of Legends fans, offering a curated selection of:
- In-game items and skins
- Official merchandise
- Exclusive accessories
- Collectibles and memorabilia

## Features

- **User Authentication**: Secure login and registration system
- **Product Catalog**: Organized display of League of Legends merchandise
- **Shopping Cart**: Seamless shopping experience
- **Payment Integration**: Secure payment processing
- **Responsive Design**: Mobile-friendly interface
- **Admin Dashboard**: Comprehensive product and order management

## Technology Stack

- **Backend**: WordPress CMS, PHP
- **Frontend**: HTML, CSS, JavaScript
- **Database**: MySQL
- **Server**: Apache/Nginx
- **Additional**: WooCommerce (for e-commerce functionality)

## Prerequisites

- PHP 7.4 or higher
- MySQL 5.6 or higher
- WordPress 5.0 or higher
- WooCommerce plugin
- Apache/Nginx web server

## Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/yourusername/riotpo.git
   ```

2. Set up your web server and point it to the `wordpress` directory

3. Create a MySQL database for the project

4. Copy `wp-config-sample.php` to `wp-config.php` and update the database settings:
   ```php
   define('DB_NAME', 'your_database_name');
   define('DB_USER', 'your_database_user');
   define('DB_PASSWORD', 'your_database_password');
   ```

5. Install and activate required WordPress plugins:
   - WooCommerce
   - Any additional plugins specified in the documentation

6. Import the database schema and initial data

7. Configure your web server settings and permissions

## Project Structure

```
riotpo/
├── web/          # WordPress core files
├── docs/              # Documentation and assets
│   ├── products/      # Product images and data
│   └── logo/          # Brand assets
└── README.md          # Project documentation
```

## License

This project is licensed under the MIT License - see the [LICENSE](wordpress/license.txt) file for details.


