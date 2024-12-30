A [Docker](https://www.docker.com/)-based test project with the [Symfony](https://symfony.com) web framework


## Features
* Docker skeleton from [symfony-docker](https://github.com/dunglas/symfony-docker)
* Symfony 7.2
* PHP 8.3
* postgres (PostgreSQL) 16.6
* Kafka 2.8.1


## Getting Started

1. If not already done, [install Docker Compose](https://docs.docker.com/compose/install/) (v2.10+)
2. If not already done, [install Make](https://www.gnu.org/software/make/) or see Makefile and run commands manually
3. Run `make init` to initialize the project
4. Wait for the project to start; migrations and fixtures will be applied automatically

## Documentation & management
* API endpoints documentation: https://localhost:5000/api/doc
* Admin panel interface:       https://localhost:5000/admin
    * Username: admin
    * Password: password
* Kafka AKHQ admin panel       http://localhost:8080/
    * Username: admin
    * Password: password

## Credentials
* admin [ROLE_ADMIN]
    * Username: admin
    * Password: password
* manager [ROLE_MANAGER]
    * Username: manager
    * Password: password
* External API key: external-api-secret-key

## Additional features
* Use the `mock:products` command to seed the products table with additional products via kafka. For faster usage, use the Make command: `make symfony arg="mock:products"`
* Order reports can be generated via the `external_api/order_report` endpoint. Reports are stored in `public/reports/orders`.