---
layout: default
title: Requirements
---

# Requirements

For a development installation, you'll need at least:

- PHP >= 7.2 with the following extensions:
    - curl
    - dom
    - fileinfo
    - gd
    - intl
    - json
    - mbstring
    - openssl
    - pdo
    - pdo_mysql
    - simplexml
    - zip
- MySQL/MariaDB >= 8.0
- composer >= 2
- node.js >= 10
- npm >= 6

It's also highly recommended developing on an UNIX-like OS.

> **For mysql >= 8.0**, there is an additional step.
>  
>  You have to go into your terminal and type the following commands.
>
> ```bash
>     mysql -u**** -p
>     set global sql_mode='';
>     exit;
> ```
