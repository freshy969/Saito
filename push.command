#!/usr/bin/env bash

cd "$(dirname "$0")"
git add .;
git commit -m 'gh-page update';
git push origin gh-pages;