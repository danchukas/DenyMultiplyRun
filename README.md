# DenyMultiplyRun

[![Build Status](https://travis-ci.org/danchukas/DenyMultiplyRun.svg?branch=master)](https://travis-ci.org/danchukas/DenyMultiplyRun)
[![Dependency Status](https://www.versioneye.com/user/projects/5968a3bf6725bd0067253c64/badge.svg?style=flat-square)](https://www.versioneye.com/user/projects/5968a3bf6725bd0067253c64)
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/279f7dc7abed4027819beb644620fd88)](https://www.codacy.com/app/volk160590/DenyMultiplyRun?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=danchukas/DenyMultiplyRun&amp;utm_campaign=Badge_Grade)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/danchukas/DenyMultiplyRun/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/danchukas/DenyMultiplyRun/?branch=master)
[![Code Coverage](https://codecov.io/gh/danchukas/DenyMultiplyRun/branch/master/graph/badge.svg)](https://codecov.io/gh/danchukas/DenyMultiplyRun)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/5bcced71-d02e-40b8-bf9c-0cbff1f473e8/mini.png)](https://insight.sensiolabs.com/projects/5bcced71-d02e-40b8-bf9c-0cbff1f473e8)

utility is work on production middlewhere, but this project for see how work github, modules, integrates with services and phpstorm.



для уникнення повторного запуска скрипта (по крону, з консолі, ...)

    $pid_file = "/var/run/some_job_or_daemon.pid";
    DenyMultiplyRun::setPidFile($pid_file);
    
    // @something_do
    
    // not necessary
    DenyMultiplyRun::deletePidFile($pid_file);


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
