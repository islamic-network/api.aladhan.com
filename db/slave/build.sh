#!/bin/bash

## Release tag / version. If this is not for a specific release, please set this to latest, otherwise set it to a specific release.
version=slave
prod=quay.io/islamic-network/api.aladhan.com-db

echo "Building production image"
docker build -f Dockerfile . -t $prod:$version
docker push $prod:$version

