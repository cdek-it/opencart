#!/bin/bash

if [ -z "$1" ]; then
  echo "Error: The path to the OpenCart folder is not specified."
  exit 1
fi

# Admin files
cp "src/upload/admin/controller/extension/shipping/cdek_official.php" "$1/upload/admin/controller/extension/shipping"
cp "src/upload/admin/language/en-gb/extension/shipping/cdek_official.php" "$1/upload/admin/language/en-gb/extension/shipping"
cp "src/upload/admin/model/extension/shipping/cdek_official.php" "$1/upload/admin/model/extension/shipping"
cp "src/upload/admin/view/template/extension/shipping/cdek_official.twig" "$1/upload/admin/view/template/extension/shipping"

# Catalog files
cp "src/upload/catalog/controller/extension/shipping/cdek_official.php" "$1/upload/catalog/controller/extension/shipping"
cp "src/upload/catalog/language/en-gb/extension/shipping/cdek_official.php" "$1/upload/catalog/language/en-gb/extension/shipping"
cp "src/upload/catalog/model/extension/shipping/cdek_official.php" "$1/upload/catalog/model/extension/shipping"
cp "src/upload/catalog/view/theme/default/template/extension/shipping/cdek_official.twig" "$1/upload/catalog/view/theme/default/template/extension/shipping"

echo "Files have been copied successfully."