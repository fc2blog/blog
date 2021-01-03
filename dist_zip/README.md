# Create distribution zip

Create a ZIP file for distribute.

## IMPORTANT NOTICE

The script will be make a zip that cloned from `https://github.com/uzulla/fc2blog` (not `fc2blog/blog`).

This situation is temporary on the development. will be change to `fc2blog/blog`.

## build zip

```
$ make build
$ ls fc2blog_dist_*
fc2blog_dist_*****.zip
```

`fc2blog_dist_*****.zip` is distributable zip. (***** is short commit id)

that contain `app`, `public`, and `app/vendor/`(libraries that installed by the composer).

## test on Ubuntu vm(docker)

```
$ make test
{some logs output}
If you want exit. please exit or ctrl-D
==================================
http://172.17.0.2/admin/common/install
==================================
{some logs output}
root@2792c09097ef:/# exit
```

> All data is not permanent. All data will be lost when exited.

## clean

```
$ make clean
```
