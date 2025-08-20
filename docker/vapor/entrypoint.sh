#!/bin/sh

# Check if we're running locally with RIE
if [ -z "$AWS_LAMBDA_RUNTIME_API" ]; then
    echo "Running locally with AWS Lambda RIE"
    exec /opt/aws-lambda-rie /opt/bootstrap
else
    echo "Running in AWS Lambda environment"
    exec /opt/bootstrap
fi
