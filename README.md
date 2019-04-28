## بِسْمِ اللهِ الرَّحْمٰنِ الرَّحِيْمِ

[![CircleCI](https://circleci.com/gh/islamic-network/api.aladhan.com.svg?style=shield)](https://circleci.com/gh/islamic-network/api.aladhan.com)
[![](https://img.shields.io/docker/pulls/vesica/api.aladhan.com.svg)](https://cloud.docker.com/u/vesica/repository/docker/vesica/api.aladhan.com)
[![](https://img.shields.io/github/release/islamic-network/api.aladhan.com.svg)](https://github.com/islamic-network/api.aladhan.com/releases)
[![](https://img.shields.io/github/license/islamic-network/api.aladhan.com.svg)](https://github.com/islamic-network/api.aladhan.com/blob/master/LICENSE)

# AlAdhan API - api.aladhan.com

This repository powers the AlAdhan.com API on http://api.aladhan.com.

# Technology Stack
* PHP 7.2
* PerconaDB 5.7
* Memcached 1.5
* Slim Framework v3
* Google Maps API Key for the GeoCoding API (https://developers.google.com/maps/documentation/geocoding/intro)
* AskGeo API Key and Account ID for the Timezone API (https://askgeo.com/database/TimeZone)

### Running the App

The api and all its dependencies are fully Dockerised. You **just need docker and docker-compose** to spin everything up.

You should enter your Google Maps API Key and Ask Geo Account ID and keys in the docker-compose file on lines 27, 28 and 29.

A production ready Docker image of the app is published as quay.io/islamic-network/api.aladhan.com on Quay.io.

To get your own instance up, simply run:

```
docker-compose up
``` 

This will bring up several containers:

1. aa-app - This is the actual PHP / Apache instance. This runs on https://localhost:7070 - see https://localhost:7070/v1/status.
2. aa-db - This is the Percona DB Container.
3. aa-memcached - This is the Memcached Container.
4. aa-pma - PHPMYAdmin to acccess your Percona DB. This runs on https://localhost:7071. The default username and password are both 'aladhan'.
5. aa-memadmin - PHPMemcachedAdmin to access your Memcached container. This runs on localhost:7074. The default username and password are both 'aa'

#### Build and Contribute

**Please note that the Dockerfile included builds a production ready container which has opcache switched on and xdebug turned off, so you will only see your changes every 5 minutes if you are developing. To actively develop, change the ```FROM vesica/php72:latest``` line to ```vesica/php72:dev```.**

With the above ```docker-compose up``` command your code is mapped to the aa-app docker container. You can make any changes and simply refresh the page to see them in real-time.

## Scaling and Sizing

This app takes 19 MB per apache process / worker and is set to have a maximum of 50 Apache workers.

A single instance should be sized with a maximum of 1152 MB RAM, after which you should scale it horizontally.

## Contributing Code

You can contribute code by raising a pull request.

There's a backlog of stuff under issues for things that potentially need to be worked on, so please feel free to pick something up from there or contribute your own improvements.

You can also join the Islamic Network Discord Server to discuss of the apps or APIs @ https://discord.gg/FwUy69M.
