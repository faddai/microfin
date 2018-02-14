# microfin

A micro-finance solution written using the Laravel web framework.

## Getting Started

These instructions will get you a copy of the project up and running on your local machine for development and testing purposes. See deployment for notes on how to deploy the project on a live system.

### Prerequisites

* [Composer](https://getcomposer.org/doc/00-intro.md)
* \>= PHP 7.1
* Laravel [Homestead](https://laravel.com/docs/5.5/homestead) or [Valet](https://laravel.com/docs/5.5/valet)

### Installing

Please run the following commands to setup your development env up.

```bash
# clone this repository
git clone https://github.com/faddai/microfin.git

# change directory
cd microfin/

# install the project's dependencies using Composer
composer install

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

End with an example of getting some data out of the system or using it for a little demo

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

