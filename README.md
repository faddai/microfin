# microfin

A simple micro-finance solution to help you manage your Customers, Loans, Investments and Savings. microFin has an 
integrated real time accounting and financial reporting you need in your financial institution.

[![Build Status](https://travis-ci.org/faddai/microfin.svg?branch=master)](https://travis-ci.org/faddai/microfin)
[![codecov](https://codecov.io/gh/faddai/microfin/branch/master/graph/badge.svg)](https://codecov.io/gh/faddai/microfin)

## Getting Started

These instructions will get you a copy of the project up and running on your local machine for development and testing 
purposes. See deployment for notes on how to deploy the project on a live system.

### Prerequisites

* [Composer](https://getcomposer.org/doc/00-intro.md)
* [Bower](https://bower.io/)
* \>= PHP 7.1.3

### Installation

Please run the following commands to setup up your development environment.

```bash
# clone this repository
git clone https://github.com/faddai/microfin.git

# change directory
cd microfin/

# install the project's dependencies using Composer
composer install

# install frontend dependencies using Bower
bower install

# make a copy of the .env.example to configure the application 
# for your local environment
cp .env.example .env
```

**Update your `.env` file with appropriate values for your database, cache, mail, etc,**

```bash
# run the migrations together with the seeders to setup the database
php artisan migrate --seed
```

Run the setup command to create a Super admin user for the application

```bash
php artisan microfin:setup
```
### Running the application

If your development environment is set up using 
[Homestead](https://laravel.com/docs/5.5/homestead) or [Valet](https://laravel.com/docs/5.5/valet), please follow 
their respective guide to run the project in the web browser.

Otherwise, you can use the development server the ships with Laravel by running, from the project root:

```bash
php artisan serve
```
You can then visit [http://localhot:8000](http://localhot:8000) to see the application in action.

## Running the tests

The tests have been grouped into Unit (Jobs, Entities and Listeners), Feature (Controllers) and Browser (end-to-end) 
inside the `tests/` directory.

```bash
# to run all Unit tests
phpunit tests/Unit

# to run all Feature tests
phpunit tests/Feature

# to run Browser tests
php artisan dusk
```

## Deployment

TDB

## Built With

* [Laravel](https://laravel.com) - The web framework used
* [Bootstrap](https://getbootstrap.com/) - The CSS framework used

## Contributing

TDB

## Authors

* **Francis Addai** - *Initial work* - [faddai](https://github.com/faddai)


## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details

