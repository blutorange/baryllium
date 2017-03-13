#!/bin/bash
mkdir docs
mkdir docs/build
phploc --log-xml docs/build/phploc.xml private/php public/servlet public/controller && phpdox -f phpdox.xml;
