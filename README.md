```
#   Setup + Lando 
###### Local setup instructions for  - QED42 application

### A) Prerequisites:
    2. Install Lando in your system. Use the below installation link for ref.
    [https://docs.lando.dev/basics/installation.html](https://docs.lando.dev/basics/installation.html)

### B) Clone this repo: https://github.com/triptimishra11/qed42_2024 

### C) Cook the docker images recipe with lando:

  1. Make sure you have enter into the project root

  2. Run lando to initiate project build

    lando start

##### D) Import database with lando:

    lando db-import <filename>

  Check if the db imported correctly

    lando mysql

  inside the mysql bash

    show databases;

### E)  Run the composer install:

  Run below command to composer install

    lando composer install


### F)  Drupal Settings: Check settings.php has same details as we have .lando.yml file

### G)  Import Config files and clear cache:

  Import Configs

    lando drush cim

  Clear Cache

    lando drush cc
