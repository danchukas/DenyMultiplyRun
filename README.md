# DenyMultiplyRun

[![Codacy Badge](https://api.codacy.com/project/badge/Grade/279f7dc7abed4027819beb644620fd88)](https://www.codacy.com/app/volk160590/DenyMultiplyRun?utm_source=github.com&utm_medium=referral&utm_content=danchukas/DenyMultiplyRun&utm_campaign=badger)
[![Build Status](https://travis-ci.org/danchukas/DenyMultiplyRun.svg?branch=master)](https://travis-ci.org/danchukas/DenyMultiplyRun)
[![Code Coverage](https://codecov.io/gh/danchukas/DenyMultiplyRun/branch/master/graph/badge.svg)](https://codecov.io/gh/danchukas/DenyMultiplyRun)


для уникнення повторного запуска скрипта (по крону, з консолі, ...)

    $file_name = "/var/run/some_job_or_daemon.pid";
    DenyMultiplyRun::setPidFile($file_name);
    // @something_do
    DenyMultiplyRun::deletePidFile($file_name);


Deny multiply run php-code while previous process exist. 
Use for cron job or daemon.

Концепція розробки яка автором закладена:
1. якщо ексепшини не викидується - 100% все відпрацювало правильно. 
    В тому числі і натівні функцції php. 
    Будь-які помилки викинуть Exception з детальним повідомленням 
    для подальшої можливості коректної обробки в проектах
     і подальшого виправлення. 
2. максимальна інформативність при будь-яких збоях, 
    що можуть виникнути при роботі бібліотеки. 
    Кожен Exception має мати: 
    * унікальний клас
    * максимально детальне повідомлення про причину і що робити.  
3. максимальна зручність і простота використання.
    
4. максимальна зручність і простота тестування. Адже чим простіше - тим: 
    
    4.1. менше зусиль для тестів.

    4.2. менша ймовірність помилок що не покриті тестами.

    4.3. швидший поріг 100% і часткового входження в проект.

5. повна інтеграція з PHPSTORM 2017.1.4 (з включеними перевірками на стандарти)

=В реалізації:=
* статичнийа не динамічний визов методів допомагає 
    - обійтись без new 
    - уникнути мороки з передачею об'єкта в інші місця програми.
* кслас з статичними методами а не просто функції для: 
    - не засорювання поля USE де використовуватиметься бібліотека,
    - приховання допоміжних методів/змінних від автодоповнювача.
