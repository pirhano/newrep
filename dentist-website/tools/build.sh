#!/bin/sh
# Build the installable WordPress zips into dist/.
#   sh tools/build.sh
set -e
cd "$(dirname "$0")/.."
mkdir -p dist
rm -f dist/rhomija-theme.zip dist/rhomija-booking.zip
(cd wordpress/themes && zip -qr -X ../../dist/rhomija-theme.zip rhomija -x '*.DS_Store')
(cd wordpress/plugins && zip -qr -X ../../dist/rhomija-booking.zip rhomija-booking -x '*.DS_Store')
ls -l dist/*.zip
