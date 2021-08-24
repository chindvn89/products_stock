## Basic infrastructure
- Use laravel framwork, version 8.x
- Use dingo api

# Installation
- Run command ```composer install``` to install the packages
- Config file ```.env``` for your environment
- Run command ```php artisan migrate``` to migrate database
- Run and check if it works with a simple endpoint: /api/products

# Run unit/feature tests
- Config file ```.env.tesing``` for testing environment
- Run command ```php artisan config:cache --env=testing``` to switch to testing mode
- Run command ```php artisan migrate``` to migrate database of testing environment
- Run command ```php artisan test``` in testing mode to run the test cases

# Run E2E tests with laravel/dusk package
- Run server ```sudo php artisan serve --port=8000```
- Config in ```.env.testing``` : APP_URL=http://localhost:8000
- Run ```php artisan dusk``` to run E2E test cases
