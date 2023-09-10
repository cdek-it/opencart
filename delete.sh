#!/bin/bash

if [ -z "$1" ]; then
  echo "Error: The path to the OpenCart folder is not specified."
  exit 1
fi

rm "$1/upload/admin/controller/extension/shipping/cdek_official.php"
rm "$1/upload/admin/language/en-gb/extension/shipping/cdek_official.php"
rm "$1/upload/admin/model/extension/shipping/cdek_official.php"
rm "$1/upload/admin/view/template/extension/shipping/cdek_official.twig"

rm -r "$1/upload/admin/view/javascript/cdek_official"
rm -r "$1/upload/admin/view/stylesheet/cdek_official"

rm "$1/upload/catalog/controller/extension/shipping/cdek_official.php"
rm "$1/upload/catalog/language/en-gb/extension/shipping/cdek_official.php"
rm "$1/upload/catalog/model/extension/shipping/cdek_official.php"
rm "$1/upload/catalog/view/theme/default/template/extension/shipping/cdek_official.twig"

rm -r "$1/upload/system/library/cdek_official"

echo "Files have been deleted successfully."