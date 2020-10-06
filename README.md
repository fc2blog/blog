blog
====

Open Source Blog

#### Operating Environment
* PHP 7.3 or higher
* MySQL 5.5 or higher


#### Installation

1. Download the .git or .zip file, and extract it to the server.  
app  
public [public directory]
　  
2. Change the name of the settings file, and store the DB or Server information.  
app/config.sample.php -> app/config.php  

 *If the `app` directory does not exist on the same level as the `public` directory,
you will need to amend the path from the following area in the `index.php`, `admin/index.php` files.  

```
$app_dir_path = __DIR__ . "/../app";
to
$app_dir_path = "/path/to/your/www/app";
```
　  
3. Access the Installation Screen  
[DOMAIN]/admin/common/install
　  
4. Follow the instructions and complete installation.  
　  
5. Once installation is complete, admin/install.php will no longer be necessary. Please delete it.


#### development with Docker.

1. `docker-compose up` at repo root directory. wait startup.
2. open `http://localhost:8080/admin/common/install`

* If you want to test https. open `https://localhost:8480/admin/common/install` .


#### unit test.

##### prepare

1. `$ composer install`
2. `$ cd tests/test_images`
3. `$ ./download_samples.sh`

> download sample photos.

##### fix permission (optional) 

If you run on different UID between install( `/admin/common/install` ) and unit testing, (ex: Install on Apache and Run phpunit with CLI)

You should be run `sudo chmod -R 777 app/temp` for fix permissions.

##### run PHPUnit

1. `$ composer run test`

#### e2e test (headless browser test)

##### prepare

1. install node.js
2. `$ cd e2e_test`
3. `$ npm ci`

##### run jest

1. startup fc2blog.
2. `$ npm run test`
