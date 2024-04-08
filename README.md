# My Orders Project

This is a sample Symfony project with basic features.
1. Create new order
2. Get all orders with filters : orderId and status
3. Update order's status using orderId and status
4. Task scheduling to generate delayed orders
5. Test cases for the first 3 features

## Installation

Follow these steps to install and run the project:

1. Clone the repository:

   ```bash
   git clone https://github.com/ramarjunamako/symfony.git

2. Install the dependencies:
    
    ```bash
    composer install

3. Update env file:
    Rename .env.example to .env and update your database details

3. Run the database migrations

    ```bash
    php bin/console doctrine:migrations:migrate

4. Start the development server
    ```bash
    symfony serve

## API Documentation

1. You can find the API documentation for this project at the following URL:
   ```bash
    http://localhost:8000/api/doc

## Seed items data
1. Run this command to feed sample data for items before creating orders
    ```bash
    php bin/console doctrine:fixtures:load

## Command to process delayed orders
This command will update all the orders with elivery date exceeded with the current date time and update
the status as delayed
    ```bash
    php bin/console app:schedule-task


## Run test cases
1. Run the below command to execute unit tests
    ```bash
    php vendor/bin/phpunit src/Tests/OrderControllerTest.php