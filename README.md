# macys-billboard

This is a custom implementation built for Macy's Time Square Billboard activation.

For full tech specs, please refer to the [Functional Requirements](https://drive.google.com/a/olapic.com/file/d/0B32ubiZK92MCcGhOdExiVWE5d2VFazNKajRLaWwyVTZRaFFz/view) documentation.

## This project uses...

This project uses Vagrant box + Silex as the API framework.

* Vagrant with trusty64 box
	* Apache
	* PHP 5.5
	* MySQL

## Pre-requisites

If you already set up [Olapic VM](https://github.com/Olapic/Puppet/blob/local/docs/Installation.md) on your machine already, you can skip this part.

* [vagrant](http://www.vagrantup.com/) (version 1.4 at least, downloaded from their site)
* [virtualbox](https://www.virtualbox.org/) (version 4.3.8 at least, 4.3.10 preferably, downloaded from their site)
* [homebrew](http://brew.sh/) (follow the instructions at the bottom of their page, you will need your AppleID setup)
* [php](http://php.net/) ([click here for instructions to install it with homebrew](PHP.md))
* [composer](https://getcomposer.org/) (with `brew install homebrew/php/composer --ignore-dependencies`, after installing php as directed)
