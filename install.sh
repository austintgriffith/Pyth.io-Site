#!/bin/bash
sudo apt-get install -y php
wget https://github.com/gohugoio/hugo/releases/download/v0.29/hugo_0.29_Linux-64bit.deb
sudo dpkg -i hugo*.deb
hugo version
