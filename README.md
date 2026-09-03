# SafeTrade SA

SafeTrade SA is a PHP and MySQL consumer-to-consumer marketplace application designed to provide a safer environment for buyers and sellers to trade products online.

The system supports buyers, sellers, and administrators with role-based access control, product management, messaging, order processing, seller verification, reviews, and a demonstration payment workflow.

## Features

### Buyer

- Register and log in as a buyer
- Browse the marketplace
- Search and filter products
- View product details
- Contact sellers
- Continue buyer-seller conversations
- Place orders
- View order history
- Complete demonstration payments
- Review sellers after completed transactions

### Seller

- Register and log in as a seller
- Create product listings
- Edit product listings
- Mark products as sold or active
- Delete unused product listings
- View and manage personal listings
- Receive buyer messages
- Reply to buyer conversations
- Accept, complete, or cancel orders
- Request seller verification

### Administrator

- Access a dedicated administration dashboard
- View registered users
- Monitor marketplace products
- Approve or reject seller verification requests
- Monitor orders
- Monitor payments

## Security Features

SafeTrade SA includes several security and validation controls:

- Passwords are stored using PHP password hashing
- Role-based access control for buyers, sellers, and administrators
- Session regeneration after successful login
- Protected authenticated routes
- Server-side form validation
- Prepared SQL statements using PDO
- Product ownership checks
- Secure image validation and random uploaded-image filenames
- Duplicate seller reviews prevented at application and database level
- Reviews restricted to completed transactions
- Order creation protected with database transactions and row locking
- Product deletion blocked when transaction or communication history exists

## Technologies

- PHP
- MySQL
- PDO
- HTML5
- CSS3
- JavaScript
- AMPPS
- phpMyAdmin
- Git
- GitHub

## Database

The application uses six main tables:

- users
- products
- messages
- reviews
- orders
- payments

The canonical database definition is stored in:

    database/schema.sql

The schema has been tested by importing it into a temporary database and confirming that all required tables and constraints are created successfully.

## Installation

### 1. Clone the repository

Run:

    git clone https://github.com/AndiTheJunior/SafeTrade-SA.git

### 2. Place the project in the AMPPS web directory

Example:

    C:\Program Files\Ampps\www\SafeTrade-SA2

### 3. Start Apache and MySQL

Open AMPPS and make sure Apache and MySQL are running.

### 4. Create the database

Open phpMyAdmin and create a database named:

    safetrade

### 5. Import the schema

Import:

    database/schema.sql

into the safetrade database.

### 6. Configure the database connection

Database configuration is stored in:

    config/database.php

Update the connection details if your MySQL credentials differ from the local development environment.

### 7. Open the application

Visit:

    http://localhost/SafeTrade-SA2/

## Account Roles

Users can register publicly as either:

- Buyer
- Seller

Administrator accounts cannot be created using the public registration form.

## Marketplace Workflow

A seller creates a product listing.

The product becomes available in the marketplace with an active status.

A buyer can:

1. Browse or search for the product.
2. Open the product details page.
3. Contact the seller.
4. Place an order.

When an order is successfully placed, the product is marked as sold so another active order cannot be placed for the same product.

The seller can then accept or cancel the order.

An accepted order can proceed to the demonstration payment process.

After the order has been completed, the buyer may submit one review for that seller and product.

## Order Statuses

SafeTrade SA uses the following order statuses:

- pending
- accepted
- completed
- cancelled

## Payment Workflow

Payments use the following statuses:

- pending
- paid
- failed
- refunded

The current payment feature is a manual demonstration workflow. It does not process real money or connect to an external payment gateway.

## Seller Verification

Seller verification uses the following states:

- unverified
- pending
- verified

A seller can request verification.

An administrator can review pending verification requests and approve or reject them.

Verified sellers display a verification indicator in the marketplace.

## Project Structure

SafeTrade-SA2/

    Admin/
        index.php
        orders.php
        payments.php
        products.php
        users.php
        verification.php

    assets/
        css/
            style.css
        js/
            app.js

    buyer/
        checkout.php
        orders.php

    config/
        database.php

    controllers/
        PaymentController.php

    database/
        schema.sql

    includes/
        auth.php
        footer.php
        header.php
        role-auth.php

    models/
        Payment.php

    seller/
        orders.php

    uploads/
        products/

    buyer-conversation.php
    buyer-messages.php
    conversation.php
    create-product.php
    dashboard.php
    delete-product.php
    edit-product.php
    index.php
    login.php
    logout.php
    messages.php
    my-products.php
    product-details.php
    products.php
    register.php
    request-verification.php
    review-seller.php
    update-product-status.php

## Development Branch

The upgraded SafeTrade SA application is currently developed on:

    feature/safetrade-upgrade

Important upgrade checkpoints include:

    ba7687f  Upgrade SafeTrade user interface and role navigation
    ebbc61b  Harden authentication product messaging and review workflows
    c6d3cc6  Remove unused scaffold files and clean project structure

## Testing

The application has been manually regression-tested using:

- Guest access
- Buyer access
- Seller access
- Administrator access

PHP syntax validation has also been performed across all active PHP files.

The database schema has been independently import-tested in a temporary database.

## Project Status

The core SafeTrade SA web application is complete and operational.

Remaining work focuses on supporting project documentation, including:

- Entity Relationship Diagram
- Data Flow Diagram
- Use Case Diagram
- User Manual
- Project Proposal
- Presentation

## Author

Andile Mbokazi

Computer Science Student
