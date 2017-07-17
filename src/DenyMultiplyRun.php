<?php
declare(strict_types = 1);

namespace DanchukAS\DenyMultiplyRun;

use DanchukAS\DenyMultiplyRun\Exception\CloseFileFail;
use DanchukAS\DenyMultiplyRun\Exception\ConvertPidFail;
use DanchukAS\DenyMultiplyRun\Exception\DeleteFileFail;
use DanchukAS\DenyMultiplyRun\Exception\FileExisted;
use DanchukAS\DenyMultiplyRun\Exception\LockFileFail;
use DanchukAS\DenyMultiplyRun\Exception\OpenFileFail;
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
     * Для перехвата помилок що не кидають ексепшини.
     *
     * @var \Throwable
     */
    private static $lastError;


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
     */
    public static function setPidFile(string $pidFilePath)
    {
        self::preparePidDir($pidFilePath);

        try {
            $file_resource = self::createPidFile($pidFilePath);
            $pid_file_existed = false;
        } catch (FileExisted $exception) {
            $file_resource = self::openPidFile($pidFilePath);
            $pid_file_existed = true;
        }

        self::lockPidFile($file_resource);

        try {
            if ($pid_file_existed) {
                try {
                    $prev_pid = self::getPidFromFile($file_resource);
                    self::checkRunnedPid($prev_pid);
                } catch (PidFileEmpty $exception) {
                    // if file was once empty is not critical.
                    // It was after crash daemon.
                    // There are signal for admin/developer.
                    trigger_error((string)$exception, E_USER_NOTICE);
                }
                self::truncatePidFile($file_resource);
            }

            $self_pid = getmypid();
            self::setPidIntoFile($self_pid, $file_resource);

            if ($pid_file_existed) {
                /** @noinspection PhpUndefinedVariableInspection */
                $message_reason = is_null($prev_pid)
                    ? ", but file empty."
                    : ", but process with contained ID($prev_pid) in it is not exist.";
                $message = "pid-file exist" . $message_reason
                    . " pid-file updated with pid this process: " . $self_pid;

                trigger_error($message, E_USER_NOTICE);
            }
        } finally {
            try {
                self::unlockPidFile($file_resource);
            } finally {
                self::closePidFile($file_resource);
            }
        }
    }

    /**
     * @param string $pidFilePath
     *
     * @throws \Exception
     */
    private static function preparePidDir($pidFilePath)
    {
        $pid_dir = dirname($pidFilePath);

        if ("" !== $pid_dir && !is_dir($pid_dir)) {
            $created_pid_dir = mkdir($pid_dir, 0777, true);
            if (false === $created_pid_dir) {
                throw new \Exception('Директорія відсутня і неможливо створити: ' . $pid_dir);
            }
        }
    }

    /**
     * @param string $pidFilePath
     * @return resource
     * @throws FileExisted
     * @throws \Exception
     */
    private static function createPidFile($pidFilePath)
    {
        // перехоплювач на 1 команду, щоб в разі потреби потім дізнатись причину несправності.
        // помилку в записує в self::$lastError
        self::startErrorHandle();

        // собачка потрібна щоб не засоряти логи.
        /** @noinspection PhpUsageOfSilenceOperatorInspection */
        $pid_file_handle = @fopen($pidFilePath, 'x');

        // Відновлюєм попередній обробник наче нічого і не робили.
        restore_error_handler();

        // файл не створений. сталась помилка
        if (!is_null(self::$lastError)) {
            self::createPidFileFailed($pidFilePath);
        }

        // файл створений успішно.
        return $pid_file_handle;
    }

    private static function startErrorHandle()
    {
        set_error_handler([__CLASS__, 'errorHandle']);

        self::$lastError = null;
    }

    /**
     * @param $pidFilePath
     * @throws FileExisted
     */
    private static function createPidFileFailed($pidFilePath): void
    {
// Файла і нема і не створився - повідомляєм про несправність проекта.
        if (!is_file($pidFilePath)) {
            throw new self::$lastError;
        }

        // Файл вже існує, тому не створився.
        throw new FileExisted($pidFilePath);
    }

    /**
     * @param string $pidFilePath
     * @return resource
     * @throws \Exception
     */
    private static function openPidFile($pidFilePath)
    {
        self::startErrorHandle();
        try {
            /** @noinspection PhpUsageOfSilenceOperatorInspection */
            $pid_file_handle = @fopen($pidFilePath, 'r+');
        } catch (\Throwable $error) {
            self::$lastError = $error;
        } finally {
            restore_error_handler();
        }

        if (!is_null(self::$lastError)) {
            throw new OpenFileFail((string) self::$lastError);
        }

        /** @noinspection PhpUndefinedVariableInspection */
        return $pid_file_handle;
    }

    /**
     * @param $pidFileResource
     *
     * @throws \Exception
     */
    private static function lockPidFile($pidFileResource)
    {
        $locked = flock($pidFileResource, LOCK_EX | LOCK_NB);
        if (false === $locked) {
            $error = self::$lastError;

            // перехоплювач на 1 команду, щоб в разі потреби потім дізнатись причину несправності.
            // помилку в записує в self::$lastError
            self::startErrorHandle();

            // собачка потрібна щоб не засоряти логи.
            /** @noinspection PhpUsageOfSilenceOperatorInspection */
            $resource_data = @stream_get_meta_data($pidFileResource);

            // Відновлюєм попередній обробник наче нічого і не робили.
            restore_error_handler();

            throw new LockFileFail($resource_data['uri'] . ' - ' . $error);
        }
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
            throw new ReadFileFail("pid-файл є, але прочитати що в ньому не вдалось.");
        }

        $pid_int = self::validatePid($pid_from_file);

        return $pid_int;
    }

    /**
     * @param string $pid_from_file
     * @return int
     * @throws PidBiggerMax
     * @throws PidFileEmpty
     */
    private static function validatePid(string $pid_from_file): int
    {
        // На випадок коли станеться виліт скрипта після створення файла і до запису ІД.
        self::pidIsNoEmpty($pid_from_file);

        $pid_int = (int) $pid_from_file;

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
    private static function pidIsNoEmpty(string $pid): void
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
    private static function pidIsPossible($pid_int): void
    {
        if ($pid_int < 0) {
            $message = "PID in file has unavailable value: $pid_int. PID must be no negative.";
            throw new PidLessMin($message);
        }

        // if PID not available - why it happens ?
        // For *nix system
        $pid_max_storage = "/proc/sys/kernel/pid_max";
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
    private static function checkRunnedPid($pid)
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
     * @param $pidFileResource
     *
     * @throws \Exception
     */
    private static function truncatePidFile($pidFileResource)
    {
        $truncated = ftruncate($pidFileResource, 0);
        if (false === $truncated) {
            throw new \Exception("не вдалось очистити pid-файл.");
        }

        $cursor_to_begin = rewind($pidFileResource);
        if (!$cursor_to_begin) {
            throw new \Exception("не вдалось перемістити курсор на початок pid-файла.");
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
        $self_pid = '' . $self_pid;
        $pid_length = strlen($self_pid);
        $write_length = fwrite($pidFileResource, $self_pid, $pid_length);
        if ($write_length !== $pid_length) {
            throw new \Exception("не вдось записати pid в pid-файл. Записано $write_length байт замість $pid_length");
        }
    }

    /**
     * @param $pidFileResource
     */
    private static function unlockPidFile($pidFileResource)
    {
        $unlocked = flock($pidFileResource, LOCK_UN | LOCK_NB);
        if (false === $unlocked) {
            trigger_error("не вдось розблокувати pid-файл.");
        }
    }

    /**
     * @param $pidFileResource
     *
     * @throws CloseFileFail
     */
    private static function closePidFile($pidFileResource)
    {
        // перехоплювач на 1 команду, щоб в разі потреби потім дізнатись причину несправності.
        // помилку в записує в self::$lastError
        self::startErrorHandle();

        try {
            // собачка потрібна щоб не засоряти логи.
            /** @noinspection PhpUsageOfSilenceOperatorInspection */
            $closed = @fclose($pidFileResource);
        } catch (\Throwable $error) {
            $closed = false;
            self::$lastError = $error;
        } finally {
            // Відновлюєм попередній обробник наче нічого і не робили.
            restore_error_handler();
        }

        if (false === $closed) {
            self::closePidFileFailed($pidFileResource);
        }
    }

    /**
     * @param $pidFileResource
     * @throws CloseFileFail
     */
    private static function closePidFileFailed($pidFileResource): void
    {
        $file_close_error = self::$lastError;

        // перехоплювач на 1 команду, щоб в разі потреби потім дізнатись причину несправності.
        // помилку в записує в self::$lastError
        self::startErrorHandle();

        try {
            // собачка потрібна щоб не засоряти логи.
            /** @noinspection PhpUsageOfSilenceOperatorInspection */
            $resource_data = @stream_get_meta_data($pidFileResource);
        } catch (\Throwable $error) {
            $resource_data = ['uri' => ''];
        } finally {
            // Відновлюєм попередній обробник наче нічого і не робили.
            restore_error_handler();
        }

        throw new CloseFileFail($resource_data['uri'], 457575, $file_close_error);
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
        // перехоплювач на 1 команду, щоб в разі потреби потім дізнатись причину несправності.
        // помилку в записує в self::$lastError
        self::startErrorHandle();

        try {
            // собачка потрібна щоб не засоряти логи.
            /** @noinspection PhpUsageOfSilenceOperatorInspection */
            @unlink($pidFilePath);
        } catch (\Throwable $error) {
            self::$lastError = $error;
        } finally {
            // Відновлюєм попередній обробник наче нічого і не робили.
            restore_error_handler();
        }

        if (!is_null(self::$lastError)) {
            throw new DeleteFileFail(self::$lastError);
        }
    }

    /**
     * @param int $messageType
     * @param string $messageText
     * @param string $messageFile
     * @param int $messageLine
     *
     * @return bool
     */
    public static function errorHandle(int $messageType, string $messageText, string $messageFile, int $messageLine)
    {
        // добавляємо лише інформацію яка є.
        // все інше добавляти має обробник самого проекта.
        $message = "[$messageType] $messageText in $messageFile on line $messageLine";

        self::$lastError = new \Exception($message);

        // Перехопити перехопили, кидаєм далі обробляти.
        return false;
    }
}
