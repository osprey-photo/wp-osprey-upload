#!/bin/bash
#
# SPDX-License-Identifier: Apache-2.0
#

# Exit on first error, print all commands.
set -e
set -o pipefail
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

function abort {
    echo "!! Exiting shell script"
    echo "!!" "$1"
    exit -1
}

zip -r wp-osprey-upload.zip "wp-osprey-upload/"

cp -r ./server/osprey/* /mnt/d/github.com/bwps/vagrant-local/www/wordpress-one/public_html/osprey
cp -r ./wp-osprey-upload/* /mnt/d/github.com/bwps/vagrant-local/www/wordpress-one/public_html/wp-content/plugins/wp-osprey-upload