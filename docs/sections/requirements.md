---
layout: default
title: Requirements
---

For a development installation, you'll need at least:

- PHP >= 5.6 with the following extensions:
    - curl
    - fileinfo
    - [gd][gd]
    - intl
    - mbstring
    - mcrypt
    - xml
    - json
    - zip
    - [ffmpeg][ffmpeg] (optional)
- MySQL/MariaDB >=5.0
- [composer][composer] (recent version)
- [node.js][node] >= 6.12
- [npm][npm] >= 3.7

It's also highly recommended to develop on an UNIX-like OS.

For mysql >= 5.7, there is an additonal step:

```
    mysql -u**** -p
    set global sql_mode='';
    exit;
```
