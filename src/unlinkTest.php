<?php
declare(strict_types=1);

try {
    unlink("/tmp/sdfsfsdf.sdf");
} catch (\Error $error) {
    if (file_exists($pidFilePath)) {
        throw new Exception($pidFilePath);
    }
    var_dump($error);

    // а якщо файла й не було - то й нехай. це не проблема даного метода.
} catch (\Throwable $throwable) {
    var_dump(get_class($throwable));
}


function a()
{
    try {
//    fopen('ddd.e');
//    include 'ddd.php';
//    strpos();
        unlink();
//    set_error_handler();
//    var_dump();
//    echo $b;
//echo $a;
//$a = 7;
//serialize($a);
    } catch (Error $error) {
        die('------------');
    }
//catch (\Throwable $throwable) {
//
//}
    print_r(error_get_last());
    print_r(error_get_last());
    error_clear_last();
    var_dump(error_get_last());

//
//
//try {
//    unlink('/tmp/dsfsdfsdfsfsdf.sdf');
//    unlink();
//} catch (Error $error) {
//    //echo $error;
//    // а якщо файла й не було - то й нехай. це не проблема даного метода.
//} catch (Throwable $throwable) {
//
//}
}