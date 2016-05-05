#!/bin/sh

find src \( -iname '*.php' \) -print0 | xargs -n1 -0 php -l && \
phpcs -psvn --standard=WordPress-Core src --ignore=js/lib/* && \
phpcs -psvn --standard=WordPress-Docs src --ignore=js/lib/* && \
phpcs -psvn --standard=WordPress-Extra --ignore=js/lib/* src
