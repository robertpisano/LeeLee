# Upgrading OS X php installation

Our system and composer require that we use php version 5.5 or later, but unfortunately OS X comes with an older version. Yosemite does come with that version, but is missing modules that you will need to compile by hand anyhow. Luckily, the process to solve either issue is the same: install the latest version using [Homebrew](http://brew.sh/).

This should help you go through the whole ordeal.

## Base software

* Install XCode using OS X's App Store
* Install Homebrew (see [instructions in their homepage](http://brew.sh/))

## PHP installation

Run the following commands:

```
brew update
brew upgrade
brew tap homebrew/dupes
brew tap homebrew/homebrew-php
brew install freetype jpeg libpng gd zlib openssl unixodbc
brew install php55
brew install php55-mcrypt
brew install php55-intl
```
This should install PHP 5.5 in your Mac, but it will not change the default one.

## Default PHP replacement

You will need to copy the corresponding binaries to the right locations (replacing the existing ones):

```
sudo cp $(brew --prefix homebrew/php/php55)/bin/php /usr/bin
sudo cp $(brew --prefix homebrew/php/php55)/bin/phpize /usr/bin
sudo cp $(brew --prefix homebrew/php/php55)/bin/php-config /usr/bin
```

If you are running MAC OS X El Capitan, you need to disable the System Integrity Protection before running the 3 previous commands.

* Reboot your Mac into Recovery Mode by restarting your computer and holding down `Command+R` until the Apple logo appears on your screen
* Click Utilities > Terminal
* In the Terminal window, type in `csrutil disable; reboot` and press Enter
* After your computer rebooted, type the 3 previous commands in a Terminal window
* Reboot your computer and get into recovery mode
* In the Terminal window, type in `csrutil enable; reboot` and press Enter
