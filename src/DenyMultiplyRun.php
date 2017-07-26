<?php
declare(strict_types = 1);

namespace DanchukAS\DenyMultiplyRun;

use DanchukAS\DenyMultiplyRun\Exception\ConvertPidFail;
use DanchukAS\DenyMultiplyRun\Exception\DeleteFileFail;
use DanchukAS\DenyMultiplyRun\Exception\FileExisted;
use DanchukAS\DenyMultiplyRun\Exception\PidBiggerMax;
use DanchukAS\DenyMultiplyRun\Exception\PidFileEmpty;
use DanchukAS\DenyMultiplyRun\Exception\PidLessMin;
use DanchukAS\DenyMultiplyRun\Exception\ProcessExisted;
use DanchukAS\DenyMultiplyRun\Exception\ReadFileFail;

/**
 * Class denyMultiplyRun
 * Забороняє паралельний запуск скрипта
 *
 * @todo: extract work with file to another lib.
 *
 * @package DanchukAS\DenyMultiplyRun
 */
class DenyMultiplyRun
{


    /**
     * @var int
     */
    private static $prevPid;


    /**
     * DenyMultiplyRun constructor.
     * Унеможливлює створення обєктів цього класу.
     * Даний клас лише для статичного визова методів.
     */
    private function __construct()
    {
    }


    /**
     * Унеможливлює паралельний запуск ще одного процеса pid-файл якого ідентичний.
     *
     * Створює файл в якому число-ідентифікатор процеса ОС під яким працює даний код.
     * Якщо файл існує і процеса з номером що в файлі записаний не існує -
     * пробує записати теперішній ідентифікатор процеса ОС під яким працює даний код.
     * В усіх інших випадках кидає відповідні виключення.
     *
     * @param string $pidFilePath Шлях до файла. Вимоги: користувач під яким запущений
     *                            даний код має мати право на створення, читання і зміну
     *                            даного файла.
     * @throws \Exception
     */
    public static function setPidFile(string $pidFilePath)
    {
        File::prepareDir($pidFilePath);

        try {
            $file_resource = File::createPidFile($pidFilePath);
            $pid_file_existed = false;
        } catch (FileExisted $exception) {
            $file_resource = File::openFile($pidFilePath, 'rb+');
            $pid_file_existed = true;
        }

        File::lockPidFile($file_resource);

        try {
            self::safeSetPidIntoFile($pid_file_existed, $file_resource);
        } finally {
            File::safeCloseFile($file_resource);
        }
    }




    /**
     * @param $pid_file_existed
     * @param $file_resource
     * @throws \Exception
     */
    private static function safeSetPidIntoFile($pid_file_existed, $file_resource)
    {
        if ($pid_file_existed) {
            self::pidNotActual($file_resource);
        }

        $self_pid = getmypid();
        self::setPidIntoFile($self_pid, $file_resource);

        if ($pid_file_existed) {
            self::pidFileUpdated($self_pid);
        }
    }

    /**
     * @param $file_resource
     * @throws \Exception
     * @throws \DanchukAS\DenyMultiplyRun\Exception\ProcessExisted
     */
    private static function pidNotActual($file_resource)
    {
        self::$prevPid = null;

        try {
            self::$prevPid = self::getPidFromFile($file_resource);
            self::pidNoExisting(self::$prevPid);
        } catch (PidFileEmpty $exception) {
            //@todo when add Debug mode fix
//            // if file was once empty is not critical.
//            // It was after crash daemon.
//            // There are signal for admin/developer.
//            trigger_error((string)$exception);
        }
        File::truncateFile($file_resource);
    }

    /**
     * @param resource $pidFileResource Дескриптор файла доступного для читання в якому знаходиться PID.
     * @return int PID з файла
     * @throws \Exception
     */
    private static function getPidFromFile($pidFileResource)
    {
        // Розмір PID (int в ОС) навряд буде більший ніж розмір int в PHP.
        // Зазвичай PID має до 5 цифр.
        // @todo: if error - warning, error_handler, ...
        $pid_from_file = fread($pidFileResource, 64);


        if (false === $pid_from_file) {
            throw new ReadFileFail('pid-файл є, але прочитати що в ньому не вдалось.');
        }

        return self::validatePid($pid_from_file);
    }

    /**
     * @param string $pid_from_file
     * @return int
     * @throws \DanchukAS\DenyMultiplyRun\Exception\ConvertPidFail
     * @throws \DanchukAS\DenyMultiplyRun\Exception\PidLessMin
     * @throws PidBiggerMax
     * @throws PidFileEmpty
     */
    private static function validatePid(string $pid_from_file): int
    {
        // На випадок коли станеться виліт скрипта після створення файла і до запису ІД.
        self::pidIsNoEmpty($pid_from_file);

        $pid_int = (int) $pid_from_file;
        //@todo when add Debug mode fix check
        // verify converting. (PHP_MAX_INT)
        // verify PID in file is right (something else instead ciphers).
        if ("{$pid_int}" !== $pid_from_file) {
            $message = "pid_int({$pid_int}) !== pid_string($pid_from_file)"
                . ", or pid_string($pid_from_file) is not Process ID)";
            throw new ConvertPidFail($message);
        }

        self::pidIsPossible($pid_int);

        return $pid_int;
    }

    /**
     * @param string $pid
     * @throws PidFileEmpty
     */
    private static function pidIsNoEmpty(string $pid)
    {
        if ('' === $pid) {
            throw new PidFileEmpty();
        }
    }

    /**
     * Verify possible value of PID in file: less than max possible on OS.
     * @param int $pid_int
     * @throws PidBiggerMax
     * @throws PidLessMin
     */
    private static function pidIsPossible($pid_int)
    {
        //@todo when add Debug mode fix check
        if ($pid_int < 0) {
            $message = "PID in file has unavailable value: $pid_int. PID must be no negative.";
            throw new PidLessMin($message);
        }

        // if PID not available - why it happens ?
        // For *nix system
        $pid_max_storage = '/proc/sys/kernel/pid_max';
        if (file_exists($pid_max_storage)) {
            $pid_max = (int)file_get_contents($pid_max_storage);
            if ($pid_max < $pid_int) {
                $message = "PID in file has unavailable value: $pid_int. In /proc/sys/kernel/pid_max set $pid_max.";
                throw new PidBiggerMax($message);
            }
        }
    }

    /**
     * @param int $pid
     *
     * @throws ProcessExisted
     */
    private static function pidNoExisting($pid)
    {
        if (// Посилає сигнал процесу щоб дізнатись чи він існує.
            // Якщо true - точно існує.
            // якщо false - процес може і бути, але запущений під іншим користувачем або інші ситуації.
            true === posix_kill($pid, 0)
            // 3 = No such process (тестувалось в Ubuntu 14, FreeBSD 9. В інших ОС можуть відрізнятись)
            // Якщо процеса точно в даний момент нема такого.
            // Для визова цієї функції необхідний попередній визов posix_kill.
            || posix_get_last_error() !== 3
        ) {
            throw new ProcessExisted($pid);
        }
    }



    /**
     * @param int $self_pid
     * @param $pidFileResource
     *
     * @throws \Exception
     */
    private static function setPidIntoFile($self_pid, $pidFileResource)
    {
        $self_pid_str = (string)$self_pid;
        $pid_length = strlen($self_pid_str);
        $write_length = fwrite($pidFileResource, $self_pid_str, $pid_length);
        if ($write_length !== $pid_length) {
            $message = "не вдось записати pid в pid-файл. Записано $write_length байт замість $pid_length";
            throw new \RuntimeException($message);
        }
    }

    /**
     * @param $self_pid
     */
    private static function pidFileUpdated($self_pid)
    {
        // @todo: maybe reference with Debug mode.
//        $message_reason = null === self::$prevPid
//            ? ', but file empty.'
//            : ', but process with contained ID(' . self::$prevPid . ') in it is not exist.';
//        $message = 'pid-file exist' . $message_reason
//            . ' pid-file updated with pid this process: ' . $self_pid;
//
//        trigger_error($message);
    }



    /**
     * Відключає встановлену заборону паралельного запуска у яких спільний $pidFilePath
     * @todo добавити перевірку що цей файл ще для цього процеса,
     * може цей файл вже був видалений вручну, і створений іншим процесом.
     *
     * @param string $pidFilePath
     *
     * @throws DeleteFileFail
     */
    public static function deletePidFile($pidFilePath)
    {
        File::deleteFile($pidFilePath);
    }


}
