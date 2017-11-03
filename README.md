# AlAdhan API - api.aladhan.com

This repository, along with a MySQL database, powers the AlAdhan.com API on http://api.aladhan.com.

# Technology Stack
* PHP 7.0
* MySQL 5.7 / PerconaDB 5.7
* Memcache
* Slim Framework v3

# Getting Started With Docker (Recommended)
You can bring up the API on docker with just one command. See <a href="https://github.com/islamic-apps/islamic-apps-docker">Uslamic Apps Docker</a> for more information.

# Getting Started Without Docker

To get started, you need to clone this repository and run 
```
composer install
```

Then copy config/config.sample.yml to config/config.yml update the values for MySQL, Memcache and your Google API key (for geocoding and timezone API access).

Then you point your http document root to the www folder and hit the host in your browser.



# Contributing 
Please read the <a href="https://github.com/islamic-apps/documentation">development guidelines</a> if you would like to contribute code.
