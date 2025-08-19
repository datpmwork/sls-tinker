ARG PHP_VERSION=82

FROM bref/php-${PHP_VERSION}-console:2 as bref

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

COPY /package /var/package

COPY /app /var/task

RUN composer dump-autoload

FROM laravelphp/vapor:php${PHP_VERSION} as vapor

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Install dependencies needed for RIE
RUN apk add --no-cache \
    curl \
    ca-certificates \
    bash \
    libc6-compat

# Download and install AWS Lambda Runtime Interface Emulator
RUN curl -Lo /opt/aws-lambda-rie \
    https://github.com/aws/aws-lambda-runtime-interface-emulator/releases/download/v1.27/aws-lambda-rie && \
    chmod +x /opt/aws-lambda-rie

COPY <<EOF /opt/bootstrap
#!/bin/sh

while true
do
    php /opt/bootstrap.php
done
EOF

# Create entrypoint script that can handle both RIE and normal Lambda execution
COPY <<EOF /opt/entrypoint.sh
#!/bin/bash

# Check if we're running locally with RIE
if [ -z "\$AWS_LAMBDA_RUNTIME_API" ]; then
    echo "Running locally with AWS Lambda RIE"
    exec /opt/aws-lambda-rie /opt/bootstrap
else
    echo "Running in AWS Lambda environment"
    exec /opt/bootstrap
fi
EOF

# Make entrypoint executable
RUN chmod +x /opt/entrypoint.sh

# Make bootstrap executable
RUN chmod +x /opt/bootstrap

# Set the CMD to use our entrypoint
ENTRYPOINT ["/opt/entrypoint.sh"]

# Expose port 8080 for RIE
EXPOSE 8080

WORKDIR /var/task

COPY /package /var/package

COPY /app/.vapor/build/app /var/task

RUN composer dump-autoload
