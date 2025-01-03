# Use an official PHP image as the base
FROM php:8.1-cli

# Set working directory inside the container
WORKDIR /app

# Install necessary dependencies and audio processing tools
RUN apt-get update && \
    DEBIAN_FRONTEND=noninteractive apt-get install -yq --no-install-recommends \
        locales \
        git \
        unzip \
        libzip-dev \
        zip \
        sox \
        libsox3 \
        libsox-dev \
        libsox-fmt-all \
        flac \
        lame \
        ffmpeg \
        atomicparsley \
        python3-mutagen \
        imagemagick && \
    docker-php-ext-install zip && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

RUN apt-get update && apt-get install -y locales && \
    echo "es_US.UTF-8 UTF-8" > /etc/locale.gen && \
    locale-gen es_US.UTF-8 && \
    update-locale LANG=es_US.UTF-8

# Set the default locale environment variables explicitly
ENV LC_ALL=es_US.UTF-8 \
    LANG=es_US.UTF-8 \
    LANGUAGE=es_US.UTF-8

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy the application files to the container
COPY . .

# Install all dependencies, including dev dependencies
RUN composer install --prefer-dist --no-scripts --no-progress --no-interaction

# Run PHPUnit tests as the default command
CMD ["vendor/bin/phpunit", "--testdox"]
