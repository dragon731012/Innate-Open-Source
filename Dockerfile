# Use the official PHP image from Docker Hub
FROM php:8.0-apache

RUN apt-get update && \
    apt-get install -y \
        zlib1g-dev \
        libzip-dev && \
    docker-php-ext-install \
        zip

RUN apt-get update && apt-get install -y \
    git \
    curl \
    && rm -rf /var/lib/apt/lists/*

# Set the working directory to the root directory
WORKDIR /var/www/html

# Copy all files from the client directory to the container's root directory
COPY ./client/ ./

# Copy the users.txt and groups.txt files to a directory accessible by Apache
COPY ./server/users.txt /var/www/html/server/
COPY ./server/groups.txt /var/www/html/server/
COPY ./server/admins.txt /var/www/html/server/
COPY ./server/groups /var/www/html/server/groups
COPY ./server/dm /var/www/html/server/dm
COPY ./server/opendm /var/www/html/server/opendm
COPY ./server/unread /var/www/html/server/unread

# Set the working directory to the server directory
WORKDIR /var/www/html/server

# Copy PHP files from the local server directory to the container's server directory
COPY ./server/*.php ./

# Ensure Apache has write permissions to the server directory and its contents
RUN chown -R www-data:www-data /var/www/html/server && chmod -R 755 /var/www/html/server

RUN apt-get update && apt-get install -y git

# Expose port 80 for Apache
EXPOSE 80

# Start Apache using the apache2-foreground command and run the script to keep the container awake in the background
CMD ["bash", "-c", "/usr/local/bin/apache2-foreground"]