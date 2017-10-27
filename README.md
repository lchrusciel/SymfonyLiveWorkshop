* Install Sylius - Standard
    * https://github.com/Sylius/Sylius-Standard#installation
        * OR
    * https://github.com/Sylius/Vagrant/#usage
* Setup workshop repository
    * git init
    * git remote add workshop git@github.com:lchrusciel/SymfonyLiveWorkshop.git
    * git checkout 0-1-describe-workshop
* Setup environment
    * bin/console sylius:install
    * yarn install
    * yarn run gulp
* Setup test environment
    * bin/console doctrine:database:create --env test
    * bin/console doctrine:schema:create --env test
