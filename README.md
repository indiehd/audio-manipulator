# IndieHD Audio Manipulator

[![Build Status](https://travis-ci.org/indiehd/audio-manipulator.svg?branch=master)](https://travis-ci.org/indiehd/audio-manipulator)
[![Coverage Status](https://codecov.io/gh/indieHD/audio-manipulator/branch/master/graph/badge.svg)](https://codecov.io/gh/indieHD/audio-manipulator)
[![Latest Stable Version](https://poser.pugx.org/indiehd/audio-manipulator/v/stable)](https://packagist.org/packages/indiehd/audio-manipulator)
[![Total Downloads](https://poser.pugx.org/indiehd/audio-manipulator/downloads)](https://packagist.org/packages/indiehd/audio-manipulator)
[![License](https://poser.pugx.org/indiehd/audio-manipulator/license)](https://packagist.org/packages/indiehd/audio-manipulator)

## About ##

The Audio Manipulator component of the indieHD Framework for PHP.

## Running Tests ##

The simplest way to run the test suite is using the included `Makefile`.

To build the Docker container:

```shell
make build
```

To start the Docker container:

```shell
make up
```

To run the test suite:

```shell
make test
```

To teardown the container:

```shell
make down
```

If for any reason using `make` is not an option, the commands in the `Makefile` can be run manually.

To log into the container for any other reason, simply run:

```
docker compose run app bash
```
