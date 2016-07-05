#!/bin/sh

find src \( -iname '*.php' \) -print0 | xargs -n1 -0 php -l && \
phpcs -psvn --standard=WordPress-Core src --ignore=plugin/js/lib/*,plugin/lib/* && \
phpcs -psvn --standard=WordPress-Docs src --ignore=plugin/js/lib/*,plugin/lib/* && \
phpcs -psvn --standard=WordPress-Extra src --ignore=plugin/js/lib/*,plugin/lib/*
