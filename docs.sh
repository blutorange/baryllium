#!/bin/bash
phploc --log-xml build/phploc.xml php && phploc --log-xml build/phploc-test.xml test/src && phpdox;
