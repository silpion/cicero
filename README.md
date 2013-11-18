
Cicero
======================

Cicero is a tool to abstract the continuous integration process for PHP Code.
Everything need to be done is creating a '.cicero.yml' file with a configuration like this:

    composer:
        enabled: true
    php:
        enabled: true
        paths:
            - "src/"
        excluded_paths:
            - "vendor/"

The aim is to run the Tools from http://phpqatools.org/ completely independent from any configuration or buildfile.
Also, a jenkins Job template will be provided for easiest setup possible, like this one http://jenkins-php.org/.


Work-In-Progress
----------------

Currently, there is no stable code release available. Still a "Work-In-Progress" project.

Development is done in the branch "developement".


LICENSE
-------

The license can be found here: [LICENSE](LICENSE)