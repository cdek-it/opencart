#!/bin/bash

if [ ! -d "src" ]; then
  echo "Error: 'src' folder not found in the current directory."
  exit 1
fi

archive_name="opencart-cdek-delivery.ocmod.zip"

cd src

zip -r ../$archive_name ./*

cd ..

mv $archive_name archive

if [ $? -eq 0 ]; then
  echo "Successfully created $archive_name."
else
  echo "An error occurred while creating the archive."
fi
