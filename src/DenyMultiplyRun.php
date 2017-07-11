<?php
declare(strict_types=1);

namespace DanchukAS\DenyMultiplyRun;

use DanchukAS\DenyMultiplyRun\Exception\CloseFileFail;
use DanchukAS\DenyMultiplyRun\Exception\DeleteFileFail;
use DanchukAS\DenyMultiplyRun\Exception\FileExisted;
use DanchukAS\DenyMultiplyRun\Exception\ProcessExisted;

/**
 * Class denyMultiplyRun
 * Забороняє паралельний запуск скрипта
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
    public static function setPidFile($pidFilePath)
    {

        self::preparePidDir($pidFilePath);

        try {
            $file_resource = DenyMultiplyRun::createPidFile($pidFilePath);
            $pid_file_empty = true;
        } catch (FileExisted $exception) {
            $file_resource = DenyMultiplyRun::openPidFile($pidFilePath);
            $pid_file_empty = false;
        }

        DenyMultiplyRun::lockPidFile($file_resource);

        try {
            // оголошено тут щоб шторм не ругався нижче, бо не може зрозуміти що змінна вже оголошена
            $prev_pid = null;
            if (!$pid_file_empty) {
                $prev_pid = DenyMultiplyRun::getPidFromFile($file_resource);
                DenyMultiplyRun::checkRunnedPid($prev_pid);
                DenyMultiplyRun::truncatePidFile($file_resource);
            }

            $self_pid = getmypid();
            DenyMultiplyRun::setPidIntoFile($self_pid, $file_resource);

            if (!$pid_file_empty) {
                $message = "pid-file exist, but process with contained ID($prev_pid) in it is not exist."
                    . " pid-file updated with pid this process: " . $self_pid;
                trigger_error($message, E_USER_WARNING);
            }
        } finally {
            try {
                DenyMultiplyRun::unlockPidFile($file_resource);
            } finally {
                DenyMultiplyRun::closePidFile($file_resource);
            }
        }

    }

    /**
     * @param $pidFilePath
     *
     * @throws \Exception
     */
    private static function preparePidDir($pidFilePath)
    {
        $pid_dir = dirname($pidFilePath);
        if (!is_dir($pid_dir)) {
            $created_pid_dir = mkdir($pid_dir, 0777, true);
            if (FALSE === $created_pid_dir) {
                throw new \Exception('Директорія відсутня і неможливо створити: ' . $pid_dir);
            }
        }
    }

    /**
     * @param $pidFilePath
     * @return resource
     * @throws FileExisted
     * @throws \Exception
     */
    private static function createPidFile($pidFilePath)
    {
        // перехоплювач на 1 команду, щоб в разі потреби потім дізнатись причину несправності.
        // помилку в записує в self::$lastError
        set_error_handler([__CLASS__, 'errorHandle']);

        // собачка потрібна щоб не засоряти логи.
        /** @noinspection PhpUsageOfSilenceOperatorInspection */
        $pid_file_handle = @fopen($pidFilePath, 'x');

        // Відновлюєм попередній обробник наче нічого і не робили.
        restore_error_handler();

        // файл не створений. сталась помилка
        if (FALSE === $pid_file_handle) {

            // Файла і нема і не створився - повідомляєм про несправність проекта.
            if (!is_file($pidFilePath)) {
                throw new \Exception("pid-файла нема, і створити не вдалось.", 897656, self::$lastError);
            }

            // Файл вже існує, тому не створився.
            throw new FileExisted($pidFilePath);
        }

        // файл створений успішно.
        return $pid_file_handle;
    }

    /**
     * @param $pidFilePath
     * @return resource
     * @throws \Exception
     */
    private static function openPidFile($pidFilePath)
    {
        $pid_file_handle = fopen($pidFilePath, 'r+');
        if (FALSE === $pid_file_handle) {
            throw new \Exception("не вдалось відкрити pid-файл для перезапису.");
        }
        return $pid_file_handle;
    }

    /**
     * @param resource $pidFileResource
     *
     * @throws \Exception
     */
    private static function lockPidFile($pidFileResource)
    {
        $locked = flock($pidFileResource, LOCK_EX | LOCK_NB);
        if (FALSE === $locked) {
            throw new \Exception("не вдалось отримати ексклюзивні права на перезапис pid-файла.");
        }
    }

    /**
     * @param \resource $pidFileResource Дескриптор файла доступного для читання в якому знаходиться PID.
     * @return int PID з файла
     * @throws \Exception
     */
    private static function getPidFromFile($pidFileResource)
    {
        // Розмір PID (int в ОС) навряд буде більший ніж розмір int в PHP.
        // Зазвичай PID має до 5 цифр.
        $pid_from_file = fread($pidFileResource, 64);

        // буває що pid = "" хоча насправді у файлі записана одиниця. чому так - не зрозуміло.
        if (FALSE === $pid_from_file || empty($pid_from_file)) {
            // @todo extract Exception
            throw new \Exception("pid-файл є, але прочитати що в ньому не вдалось.");
        }

        $pid_from_file = intval($pid_from_file, 10);

        return $pid_from_file;
    }

    /**
     * @param $pid
     *
     * @throws ProcessExisted
     */
    private static function checkRunnedPid($pid)
    {
        if (
            // Посилає сигнал процесу щоб дізнатись чи він існує.
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
     * @param \resource $pidFileResource
     *
     * @throws \Exception
     */
    private static function truncatePidFile($pidFileResource)
    {
        $truncated = ftruncate($pidFileResource, 0);
        if (FALSE === $truncated) {
            throw new \Exception("не вдалось очистити pid-файл.");
        }

        $cursor_to_begin = rewind($pidFileResource);
        if (!$cursor_to_begin) {
            throw new \Exception("не вдалось перемістити курсор на початок pid-файла.");
        }
    }

    /**
     * @param int $self_pid
     * @param resource $pidFileResource
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
     * @param resource $pidFileResource
     */
    private static function unlockPidFile($pidFileResource)
    {
        $unlocked = flock($pidFileResource, LOCK_UN | LOCK_NB);
        if (FALSE === $unlocked) {
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
        set_error_handler([__CLASS__, 'errorHandle']);

        // собачка потрібна щоб не засоряти логи.
        /** @noinspection PhpUsageOfSilenceOperatorInspection */
        $closed = @fclose($pidFileResource);

        // Відновлюєм попередній обробник наче нічого і не робили.
        restore_error_handler();

        if (FALSE === $closed) {

            $file_close_error = self::$lastError;

            // перехоплювач на 1 команду, щоб в разі потреби потім дізнатись причину несправності.
            // помилку в записує в self::$lastError
            set_error_handler([__CLASS__, 'errorHandle']);

            // собачка потрібна щоб не засоряти логи.
            /** @noinspection PhpUsageOfSilenceOperatorInspection */
            $resource_data = @stream_get_meta_data($pidFileResource);

            // Відновлюєм попередній обробник наче нічого і не робили.
            restore_error_handler();

            throw new CloseFileFail($resource_data['uri'] . ' - ' . $file_close_error);
        }
    }

    /**
     * Відключає встановлену заборону паралельного запуска у яких спільний $pidFilePath
     *
     * @param string $pidFilePath
     *
     * @throws DeleteFileFail
     */
    public static function deletePidFile($pidFilePath)
    {
        // перехоплювач на 1 команду, щоб в разі потреби потім дізнатись причину несправності.
        // помилку в записує в self::$lastError
        set_error_handler([__CLASS__, 'errorHandle']);

        try {
            // собачка потрібна щоб не засоряти логи.
            /** @noinspection PhpUsageOfSilenceOperatorInspection */
            @unlink($pidFilePath);

        } catch (\Error $error) {

            self::$lastError = $error;
        } finally {
            // Відновлюєм попередній обробник наче нічого і не робили. 
            restore_error_handler();
        }

        if (file_exists($pidFilePath)) {
            throw new DeleteFileFail(self::$lastError);
        }

        // а якщо файла й не було - то й нехай. це не проблема даного метода.
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

