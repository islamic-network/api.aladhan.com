## بِسْمِ اللهِ الرَّحْمٰنِ الرَّحِيْمِ

[![CI](https://cairo.mamluk.net/api/v1/teams/islamic-network/pipelines/api-aladhan-com/badge)](https://cairo.mamluk.net/teams/islamic-network/pipelines/api-aladhan-com)
[![](https://img.shields.io/docker/pulls/islamicnetwork/api.aladhan.com.svg)](https://cloud.docker.com/u/vesica/repository/islamicnetwork/vesica/api.aladhan.com)
[![](https://img.shields.io/github/release/islamic-network/api.aladhan.com.svg)](https://github.com/islamic-network/api.aladhan.com/releases)
[![](https://img.shields.io/github/license/islamic-network/api.aladhan.com.svg)](https://github.com/islamic-network/api.aladhan.com/blob/master/LICENSE)
![GitHub All Releases](https://img.shields.io/github/downloads/islamic-network/api.aladhan.com/total)

# AlAdhan API - api.aladhan.com

This repository powers the AlAdhan.com API on http://api.aladhan.com.

# Technology Stack
* PHP 8.2
* Memcached 1.6
* Kipchak (https://github.com/mamluk/kipchak)
* 7x APIs (https://7x.ax)

### Running the App

The api and all its dependencies are fully Dockerised. You **just need docker and docker-compose** to spin everything up.

You should enter your 7x API key in the docker-compose file on line 15.

A production ready Docker image of the app is published as:

* ghcr.io/islamic-network/api.aladhan.com on GitHub Container Registry
* quay.io/islamic-network/api.aladhan.com on Quay

To get your own instance up, simply run:

```
docker-compose up
``` 

This will bring up several containers:

1. aladhan_api - This is the actual PHP / Apache instance. This runs on http://localhost - see http://localhost/status.
3. aladhan_memcached - This is the Memcached Container.

#### Build and Contribute

**Please note that the Dockerfile included builds a production ready container which has opcache switched on and xdebug turned off, so you will only see your changes every 5 minutes if you are developing. To actively develop, change the ```FROM ghcr.io/islamic-network/php:8.1-apache``` line to ```FROM ghcr.io/islamic-network/php:8.1-apache-dev```.**

With the above ```docker-compose up``` command your code is mapped to the aa-app docker container. You can make any changes and simply refresh the page to see them in real-time.

Please run ```composer install``` from within the container or on your machine because the first time to you run ```docker-compose up```, your empty vendor directory will overwrite what is in the container.

## Scaling and Sizing

This app takes 19 MB per apache process / worker and is set to have a maximum of 16 Apache workers.

A single instance should be sized with a maximum of 356 MB RAM, after which you should scale it horizontally.

## Contributing Code 

You can contribute code by raising a pull request.

There's a backlog of stuff under issues for things that potentially need to be worked on, so please feel free to pick something up from there or contribute your own improvements.

You can also join the community at https://community.islamic.network/.
