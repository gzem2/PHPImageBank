#!/bin/bash

SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )"
php $SCRIPT_DIR/Seeder.php -n
php -S localhost:8080 $SCRIPT_DIR/public/index.php