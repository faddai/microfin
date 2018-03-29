# microfin

A micro-finance solution written using the Laravel web framework.

[![Build Status](https://travis-ci.org/faddai/microfin.svg?branch=master)](https://travis-ci.org/faddai/microfin)
[![codecov](https://codecov.io/gh/faddai/microfin/branch/master/graph/badge.svg)](https://codecov.io/gh/faddai/microfin)

## Getting Started

These instructions will get you a copy of the project up and running on your local machine for development and testing purposes. See deployment for notes on how to deploy the project on a live system.

### Prerequisites

* [Composer](https://getcomposer.org/doc/00-intro.md)
* [Bower](https://bower.io/)
* \>= PHP 7.1

### Installing

Please run the following commands to setup your development env up.

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
the guides that come with your chosen tool. 

Otherwise, you can use the development server the ships with Laravel by running, from the project root:

```bash
php artisan serve
```
You can visit [http://localhot:8000](http://localhot:8000) to see the application in action.

## Running the tests

There are tests for some Controllers, Jobs, Entities and Listeners available in the `tests/` directory.

```bash
# to run all tests
phpunit tests/
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

