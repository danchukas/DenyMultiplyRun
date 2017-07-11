<?php
declare(strict_types=1);

/**
 * Created by PhpStorm.
 * User: danchukas
 * Date: 2017-06-22 14:51
 */


namespace DanchukAS\Helper;


/**
 * Class HiddenError
 * @package DanchukAS\Helper
 */
class HiddenError
{

    /**
     * Вказівник на обробник для перехвачування помилок.
     * @var callable|array array - for phpstorm analise sniffer
     */
    private static $errorHandler = [__CLASS__, "errorHandler"];


    /**
     * Перехоплена помилка.
     * @var \Error
     */
    private static $lastError;


    /**
     * Чи генерувати Error для перехоплених помилок.
     * @var bool
     */
    private static $autoThrow;


    /**
     * Чи відключати автоматично перехоплювач після виконаного перехоплення.
     * @var bool
     */
    private static $autoDisable;


    /**
     * Чи включений перехоплювач на даний момент.
     * @var bool
     */
    private static $enabled = false;


    /**
     * HiddenError constructor.
     * Унеможливлює створення обєктів цього класу.
     * Даний клас лише для статичного визова методів.
     */
    private function __construct()
    {
    }


    /**
     * Виконує функцію з переданими параметрами.
     * При появі помилки що виникне при роботі переданої функції буде згенерований відповідний Error.
     *
     * @param callable $function
     * @param array ...$param_arr
     * @return mixed
     * @throws \Error
     */
    public function sandbox(callable $function, ... $param_arr)
    {
        self::enable();

        try {
            $return = call_user_func_array($function, $param_arr);
            // щоб в обробник не попадали помилки які з php7 генеруються за допомогою Error.
        } catch (\Error $error) {
            // прокидує помилку.
            throw $error;
        }

        // якщо попередній метод спрацював без помилок - отже треба відключити.
        // якщо була помилка, вона відловлена, і там буде відключена. сюди код не вернеться.
        self::disable();

        return $return;
    }


    /**
     * Включає режим перехоплення.
     *
     * В зоні перехоплення помилок не рекомендується визивати set_error_handler чи restore_error_handler.
     *
     * @param bool $auto_disable при відлові помилки відключить(визве метод disable) автоматично.
     * @param bool $auto_throw
     * @param int $error_types Помилки групи E_USER згідно документації - встроєнні методи пхп не кидають.
     *                         Їх і тільки їх можуть згенерувати через trigger_error.
     *                         Раз програміст в коді використав trigger_error отже йому видніше,
     *                         і при потребі сам напише свій костиль-перехоплювач, або змінить параметр цей.
     */
    public static function enable(
        $auto_disable = true
        , $auto_throw = true
        , $error_types = E_ALL & ~E_USER_WARNING & ~E_USER_ERROR & ~E_USER_NOTICE
    )
    {

        if (self::$enabled) {
            $message = "__CLASS__ is already enabled. __METHOD__ is called repeatedly."
                . " Use method __CLASS__::disable() for disable previous call.";
            throw new \LogicException($message);
        }

        self::$enabled = true;

        // щоб при наступному виклику не звлялись не актуальні вже повідомлення з минулих запусків.
        self::$lastError = null;

        self::$autoDisable = $auto_disable;

        self::$autoThrow = $auto_throw;

        self::setHandler($error_types);

    }


    /**
     * Виключає режим перехоплення.
     */
    public static function disable()
    {
        // Detect and alert excepted non-normal situation.
        // Begin

        // Підказка про логічну помилку програміста в програмі перед запуском даного методу.
        if (!self::$enabled) {
            $message = "__CLASS__ is not enabled by __CLASS__::enable(...) before or was auto disabled already by it.";
            throw new \LogicException($message);
        }

        // life hack: for get current set upped error handler.
        $current_handle = set_error_handler(function () {
        });
        // clearing for previous life hack.
        restore_error_handler();

        // Нерівність можлива лише при помилці програміста у не вірному використанні класа.
        if ($current_handle !== self::$errorHandler) {

            // Переводить в читаємий рядок назву обробника помилок.
            if (is_array(self::$errorHandler)) {
                $handler_str = implode('::', self::$errorHandler);
            } else {
                $handler_str = (string)self::$errorHandler;
            }

            $message = "__METHOD__ expects {$handler_str} on top stack of error handlers"
                . ", but {$current_handle} on top."
                . " Called set_error_handler or restore_error_handler somewhere after __CLASS__::enable. ";
            throw new \LogicException($message);
        }
        // End

        // Відключає встановлений класом обробник помилок.
        // Коректно "Відновлює" стек обробників помилок.
        // Передає контроль в найвищий обробник помилок в стеку.
        restore_error_handler();

        self::$enabled = false;

    }


    /**
     * Notice: даний метод публічний лише тому, що інакше не можливо його передати в метод set_error_handle.
     *         Визов даного методу за межами даного класа на совісті програміста що визиває.
     *
     * Обробник помилок.
     * Щоб метод не передавав помилку стандартному обробнику помилок PHP -
     * встановлений має бути self::$autoThrow,
     * або функція має бути визвана з оператором @.
     *
     * @param int $messageType
     * @param string $messageText
     * @param string $messageFile
     * @param int $messageLine
     * @return bool false Повертається false щоб не переривати оброблення помилки іншими способами.
     *                      У випадку коли self::$autoThrow відповідно подальша обрбка обірветься.
     * @throws \Error
     */
    public static function errorHandler(int $messageType, string $messageText, string $messageFile, int $messageLine)
    {
        // добавляємо лише інформацію яка є.
        // все інше добавляти має обробник самого проекта.
        $message = "[$messageType] $messageText in $messageFile on line $messageLine";

        self::$lastError = new \Error($message);

        // Відключає тут перед можливим викидом з тіла метода.
        if (self::$autoDisable) {
            self::disable();
        }

        if (self::$autoThrow) {
            throw self::$lastError;
        }

        // Передає далі обробляти.
        return false;
    }


    /**
     * @param int $error_types Дефолтне значення суто для зручності, щоб можна було метод визивати без нього.
     */
    private static function setHandler($error_types = E_ALL)
    {
        try {
            $is_success_set = set_error_handler((self::$errorHandler), $error_types);
        } catch (\Error $error) {
            throw $error;
        }

        // if set_error_handler return null then probability error handler is not set successfully.
        if (is_null($is_success_set)) {
            $current_handler = set_error_handler(function () {
            });
            restore_error_handler();

            if ($current_handler !== self::$errorHandler) {

                // Припущення що інформація про помилку з'явиться в error_get_last()
                // @todo: Перевірити припущення.
                $last_php_error = error_get_last();

                // convert $last_php_error to string
                $last_php_error = !is_null($last_php_error)
                    ? 'NULL'
                    : print_r($last_php_error, true);

                $message = "error in set_error_handler(...) running. error_get_last() = {$last_php_error}";
                throw new \RuntimeException($message);
            }
        }
    }


}


/**
 * @todo register_shutdown_function http://php.net/manual/ru/function.register-shutdown-function.php
 * @todo ob_handle fatal_error+memory_limit http://dklab.ru/chicken/nablas/45.html
 *
 *
 */
//ini_set("display_errors", "on");
//error_reporting(E_ALL);
//ini_set('html_errors', 'on');
//Как оказалось, нужно было отключить xdebug. Он меняет html фатала

//Перехват ошибки нехватки памяти
//
//Метод с ob_start() может иногда не сработать, если произошедшая фатальная ошибка — это ошибка нехватки памяти ("Allowed memory size of xxx bytes exhausted"). Она возникает, когда PHP пытается затребовать больше памяти, чем ему разрешено настройкой memory_limit файла php.ini.
//
//К счастью, выход есть и в этом случае (правда, он работает не в 100% ситуаций, а только если память выделяется относительно большими кусками):
//
//скопировать код в буфер обмена Листинг 3
//function myObHandler($str)
//{
//// Free a piece of memory.
//unset($GLOBALS['tmp_buf']);
//// Now we have additional 100K of memory, so - continue to work.
//return $str . " - output is handled!";
//}
//// Reserve 200K of memory for emergency needs.
//$GLOBALS['tmp_buf'] = str_repeat('x', 1024 * 200);
//// Handle the output stream and set a handler function.
//ob_start('myObHandler');
//// Simulate a memory limit error.
//echo str_repeat("Test string!<br>", 500);
//while(1) $tmp[] = str_repeat('a', 10000);
//
//Итак, мы резервируем некоторый объем памяти во временной переменной, которую освобождаем в первой строке обработчика выходного потока. При этом высвобождается некоторое количество памяти, которого должно хватить на корректное продолжение работы обработчика (он сам по себе может потреблять сколько-то памяти).
//
//Таким образом, можно сделать следующее заключение: при наступлении ошибки memory_limit PHP честно пытается запустить обработчик, однако, если в процессе его работы опять не хватит памяти, выполнение скрипта останавливается уже окончательно. Освободив 200К памяти в самом начале обработчика, мы оказываемся практически застрахованными от такой ситуации.
//
//Чайник
//Нужно понимать, что этот метод работает при условии, что память закончилась при выделении достаточно крупного куска (в нашем случае - 10К). В противном случае PHP может не хватить ресурсов даже на то, чтобы просто инициировать запуск обработчика выходного потока.
