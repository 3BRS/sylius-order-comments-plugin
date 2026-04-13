FROM nginx:alpine

ENV WORKING_DIR=/srv/sylius/tests/Application

WORKDIR "${WORKING_DIR}"

COPY ./nginx.conf /etc/nginx/templates/default.conf.template

ENV FASTCGI_PASS=php:9000
