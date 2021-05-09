# Create distribution zip

Create a ZIP file for distribute.

The branch name to build will be same as `git branch --contains |cut -d " " -f 2` (So, same as your local checkout
branch).

## build zip

```
$ make build
$ ls fc2blog_dist.zip
fc2blog_dist.zip
```

`fc2blog_dist.zip` is distributable zip.

that contain `app`, `public`, and `app/vendor/`(libraries that installed by the composer).

### build zip from your repo

```
$ export GITHUB_REPO_URL="https://github.com/uzulla/fc2blog.git"
$ make build
```

### build local src (no checkout)

```
$ make build-no-pushed-branch
```

## test on Ubuntu vm(docker)

```
$ make test
{some build progress...}
{After a while, will startup bash in docker. you can check something or startup apache and mysql by /startup.sh}
$ ./startup.sh
{start up apache and mysql and some logs output}
If you want exit. please exit or ctrl-D
==================================
{You can open this url by local browser}
http://172.17.0.2/admin/common/install
==================================
{some apache logs output}
root@2792c09097ef:/# exit
```

> All data is not permanent. All data will be lost when bash exited.

### test local src (no checkout)

```
$ make test-no-pushed-branch
```

## clean

```
$ make clean
```

## Fc2blog handy installer

[`installer/fc2blog_installer.php`](installer/fc2blog_installer.php)

Handy deploy tool.

Installation can be done by simply, uploading one small php file and running it.

**It works, but under development now.**

**Currently, Download zip from an unofficial repository.**

### overview

- Self download distribute zip file.
- Download zip from github release's assets.
    - **Currently, Download zip from an unofficial repository.**
- Extract and deploy fc2blog app.
- Generate `app/config.php` from user input.
- redirect to `/admin/common/install` when complete.

### how to use

1. Upload [`installer/fc2blog_installer.php`](installer/fc2blog_installer.php) to server's (VirtualHost's) document
   root.
2. Open `fc2blog_installer.php` by browser.
3. Fill some configuration and click execute button
4. Ta-da! After that, follow the normal installer.
