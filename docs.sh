#!/bin/bash
phploc --log-xml build/phploc.xml php && phpdox;
