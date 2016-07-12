# BitGo Express PHP Example

This example demonstrates the use of a PHP wrapper with BitGo Express. BitGoSDK.php implements only a very small portion of the possible applications for demonstration purposes.

# To install:

If you don't have composer already installed, download and install it from here:
https://getcomposer.org/doc/00-intro.md#globally

For version 1.1.3, the commands would be as follows:
```Shell
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
# verify the checksum for version 1.1.3
# if the verification fails because of a new version, either comment out the line, or refer to the link above
php -r "if (hash_file('SHA384', 'composer-setup.php') === 'e115a8dc7871f15d853148a7fbac7da27d6c0030b848d9b3dc09e2a0388afed865e6a3d6b3c0fad45c48e2b5fc1196ae') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
php composer-setup.php
php -r "unlink('composer-setup.php');"
mv composer.phar /usr/local/bin/composer
```

Once you have composer installed, install the dependencies for the BitGo PHP SDK.
```Shell
composer install
```

# To run:

 First, you need to point the PHP SDK to your BitGo Express instance. To do that, you can do the following:
```Shell
export BITGO_CUSTOM_ROOT_URI=http://localhost
export PORT=3080
 ```

Once that is done, you can run the examples. The SDK will infer the configuration from its environment.
The default BitGo Express location it will infer with no environment variables set is `http://localhost:3080`.

```Shell
php example.php
```

# To test:

Run BitGo Express locally on port 3080, and set its environment to test.

```Shell
composer test
```