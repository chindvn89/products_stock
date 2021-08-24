## Basic infrastructure
- Use laravel framwork, version 8.x
- Use dingo api

# Installation
- Run command ```composer install``` to install the packages
- Config file ```.env``` for your environment
- Run and check if it works with a simple endpoint: /api/products

# Run tests
- Config file ```.env.tesing``` for testing environment
- Run command ```php artisan config:cache --env=testing``` to switch to testing mode
- Run command ```php artisan test``` in testing mode to run the test cases
