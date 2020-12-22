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


#### test to use or development, with Docker.

1. Mac: run `docker-compose up`. Linux: run `make docker-compose-build` and then `docker-compose up`.  
2. open `http://localhost:8080/admin/common/install`

* If you use Docker, you may don't need to create or edit `app/config.php`.
* If you want to test https. open `https://localhost:8480/admin/common/install` .

#### unit test

```
$ make test
```

* If you want to know "what's going on?", Please see the `Makefile`.  
* Require Docker&docker-compose, PHP, node.js runtimes.
* If you got DB connection error on first run, please wait mysql initialize and retry.
