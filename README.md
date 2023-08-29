# Petshop

Petshop is a e-commerce website.
Laravel website API's

-   Admin
-   User

## Features

-   Admin Module
    -   Login
    -   Logout
    -   User Listing
-   User Module
    -   Login
    -   Logout
    -   Order Listing
-   Main Pages
    -   Promotion Listing
    -   Blog Listing
    -   Fetch Blog detail by uuid
-   Category Listing
-   Brand Listing
-   Product Module
    -   Product Create
    -   Product Listing
    -   Product Delete
    -   Product Update
-   Order Status Listing
-   Payment Module
    -   Payment Create
    -   Payment Listing
    -   Payment Delete
    -   Payment Update
-   Order Module
    -   Order Listing
    -   Order Create
    -   Order Update
-   File Module
    -   File Upload
    -   Fetch File

## Installation using Docker

Petshop is very easy to install and deploy in a Docker container.

By default, the Docker will expose port 8080, so change this within the
Dockerfile if necessary. When ready, simply use the Dockerfile to
build the image.

```sh
cp .env.example .env
```

Change below **.env** variable

```sh
DB_HOST=petshop_db
DB_DATABASE=petshop
DB_USERNAME=petshop
DB_PASSWORD=secret
```

Run following commands:

```sh
sh docker/setup.sh
```

This will create the petshop image and pull in the necessary dependencies.

Once done, run the Docker image and map the port to whatever you wish on
your host. In this example, we simply map port 80 of the host to
port 80 of the Docker (or whatever port was exposed in the Dockerfile):

```sh
sh docker/start.sh
```

```sh
docker exec -it lr_pms bash
sh setup/install.sh
```

Add domain entry to hosts file

```sh
127.0.0.1 petshop.local
```

Verify the deployment by navigating to your server address in
your preferred browser.

Check in browser

```sh
petshop.local
```
