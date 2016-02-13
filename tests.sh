#!/bin/sh

find src \( -iname '*.php' \) -print0 | xargs -n1 -0 php -l && \
phpcs -psvn --standard=WordPress-Core src && \
phpcs -psvn --standard=WordPress-Docs src && \
phpcs -psvn --standard=WordPress-Extra src
