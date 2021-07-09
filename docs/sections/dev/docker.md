---
layout: default
title: Docker
---

# Docker

## Using Docker (development only - beginner-friendly guide)

**Warning**: this is for development/testing purposes *only*, this must **NOT** be used in production environments as it represents huge security risks, maintainability issues and performance degradations.

As a developer, by using Docker you can quickly get the platform running in DEV mode and experiment with code changes.

### Requirements:

* Docker Engine (latest)
* docker-compose (latest)

**Windows 10 Users**: you can follow the official guide to install WSL 2 and then Docker Desktop:

[Docker Desktop WSL 2 backend](https://docs.docker.com/docker-for-windows/wsl/)

Then clone the project into your WSL's user folder (for example, `cd ~` and then `git clone` this repo's URL), then navigate into the new project folder (`cd Claroline`).

### Starting Docker

In the project folder, run the following command (it may take 5-10 minutes, in the end you should see that webpack-dev-server has compiled the UI, you may ignore any warnings):

```sh
docker-compose -f docker-compose.dev.yml up
```

Then you can open [http://localhost/](http://localhost/) and you should see the login page, this means that the project was started successfully.

### Login

You can use the admin credentials from the *docker-compose.dev.yml* file and you are strongly advised to change the default password.

### Development

When you modify ReactJS components, the page will automatically refresh.

When you modify PHP files, you'll need to manually refresh the page.

### Rebuilding your theme in watch mode

While running the containers, open a new console in the project folder and run the following command (replace "yourthemename" with the name of your theme):

```sh
docker exec -it claroline-web npx nodemon -e less --watch files/themes-src/yourthemename --exec 'php bin/console claroline:theme:build --theme=yourthemename'
```

If you're working on one of the core themes (the ones included with Claroline), you can adapt this command by changing the folder to watch and by passing the theme's name.
