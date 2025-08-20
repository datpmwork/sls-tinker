ARG PHP_VERSION=82
ARG PHP_VERSION_TAG=8.2
ARG LARAVEL_VERSION=10.0

FROM bref/php-${PHP_VERSION}-console:2 AS bref

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

COPY . /tmp/package
ENV COMPOSER_MIRROR_PATH_REPOS=1
RUN composer create-project "laravel/laravel:${LARAVEL_VERSION}" --no-interaction . && \
    composer config repositories.local path /tmp/package && \
    composer require datpmwork/sls-tinker:@dev && \
    composer require bref/bref:^2 --no-interaction

FROM laravelphp/vapor:php${PHP_VERSION} AS vapor

# Expose port 8080 for RIE
EXPOSE 8080

WORKDIR /var/task

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Download and install AWS Lambda Runtime Interface Emulator
RUN curl -Lo /opt/aws-lambda-rie \
    https://github.com/aws/aws-lambda-runtime-interface-emulator/releases/download/v1.27/aws-lambda-rie && \
    chmod +x /opt/aws-lambda-rie

COPY docker/vapor/bootstrap /opt/bootstrap
COPY docker/vapor/entrypoint.sh /opt/entrypoint.sh

COPY . /tmp/package
ENV COMPOSER_MIRROR_PATH_REPOS=1
RUN composer create-project "laravel/laravel:${LARAVEL_VERSION}" --no-interaction . && \
    composer config repositories.local path /tmp/package && \
    composer require datpmwork/sls-tinker:@dev && \
    composer require laravel/vapor-core --no-interaction && \
    cp vendor/laravel/vapor-core/stubs/cliRuntime.php cliRuntime.php && \
    cp vendor/laravel/vapor-core/stubs/runtime.php runtime.php

RUN composer dump-autoload

# Make entrypoint executable
RUN chmod +x /opt/entrypoint.sh
# Make bootstrap executable
RUN chmod +x /opt/bootstrap
# Set the CMD to use our entrypoint
ENTRYPOINT ["/opt/entrypoint.sh"]

ENV LAMBDA_TASK_ROOT=/var/task
ENV APP_RUNNING_IN_CONSOLE=true
ENV VAPOR_SSM_PATH=test
