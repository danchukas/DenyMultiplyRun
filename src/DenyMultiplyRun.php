<?php
declare(strict_types=1);

namespace DanchukAS\DenyMultiplyRun;

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
     * @var \Exception
     */
    private $lastError = null;

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
    public function setPidFile($pidFilePath)
    {

        $this->preparePidDir($pidFilePath);

        try {
            $file_resource = $this->createPidFile($pidFilePath);
            $pid_file_empty = true;
        } catch (FileExisted $exception) {
            $file_resource = $this->openPidFile($pidFilePath);
            $pid_file_empty = false;
        }

        $this->lockPidFile($file_resource);

        try {
            // оголошено тут щоб шторм не ругався нижче, бо не може зрозуміти що змінна вже оголошена
            $prev_pid = null;
            if (!$pid_file_empty) {
                $prev_pid = $this->getPidFromFile($file_resource);
                $this->checkRunnedPid($prev_pid);
                $this->truncatePidFile($file_resource);
            }

            $self_pid = getmypid();
            $this->setPidIntoFile($self_pid, $file_resource);

            if (!$pid_file_empty) {
                $message = "pid-file exist, but process with contained ID($prev_pid) in it is not exist."
                    . " pid-file updated with pid this process: " . $self_pid;
                trigger_error($message, E_USER_WARNING);
            }
        } finally {
            try {
                $this->unlockPidFile($file_resource);
            } finally {
                $this->closePidFile($file_resource);
            }
        }

    }

    /**
     * @param $pidFilePath
     *
     * @throws \Exception
     */
    private function preparePidDir($pidFilePath)
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
     *
     * @return resource
     * @throws \Exception
     */
    private function createPidFile($pidFilePath)
    {
        // перехоплювач на 1 команду, щоб в разі потреби потім дізнатись причину несправності.
        // помилку в записує в $this->lastError
        set_error_handler([$this, 'errorHandle']);

        // собачка потрібна щоб не засоряти логи.
        /** @noinspection PhpUsageOfSilenceOperatorInspection */
        $pid_file_handle = @fopen($pidFilePath, 'x');

        // Відновлюєм попередній обробник наче нічого і не робили.
        restore_error_handler();

        // файл не створений. сталась помилка
        if (FALSE === $pid_file_handle) {

            // Файла і нема і не створився - повідомляєм про несправність проекта.
            if (!is_file($pidFilePath)) {
                throw new \Exception("pid-файла нема, і створити не вдалось.", 897656, $this->lastError);
            }

            // Файл вже існує, тому не створився.
            throw new FileExisted($pidFilePath);
        }

        // файл створений успішно.
        return $pid_file_handle;
    }

    /**
     * @param $pidFilePath
     *
     * @return resource
     * @throws \Exception
     */
    private function openPidFile($pidFilePath)
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
    private function lockPidFile($pidFileResource)
    {
        $locked = flock($pidFileResource, LOCK_EX | LOCK_NB);
        if (FALSE === $locked) {
            throw new \Exception("не вдалось отримати ексклюзивні права на перезапис pid-файла.");
        }
    }

    /**
     * @param resource $pidFileResource Дескриптор файла доступного для читання в якому знаходиться PID.
     *
     * @return int PID з файла
     * @throws \Exception
     */
    private function getPidFromFile(resource $pidFileResource)
    {
        // Розмір PID (int в ОС) навряд буде більший ніж розмір int в PHP.
        // Зазвичай PID має до 5 цифр.
        $pid_from_file = fread($pidFileResource, 64);

        // буває що pid = "" хоча насправді у файлі записана одиниця. чому так - не зрозуміло.
        if (FALSE === $pid_from_file || empty($pid_from_file)) {
            throw new \Exception("pid-файл є, але прочитати що в ньому не вдалось.");
        }

        $pid_from_file = intval($pid_from_file, 10);

        return $pid_from_file;
    }

    /**
     * @param $pid
     *
     * @throws \Exception
     */
    private function checkRunnedPid($pid)
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
     * @param resource $pidFileResource
     *
     * @throws \Exception
     */
    private function truncatePidFile(resource $pidFileResource)
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
    private function setPidIntoFile($self_pid, $pidFileResource)
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
    private function unlockPidFile($pidFileResource)
    {
        $unlocked = flock($pidFileResource, LOCK_UN | LOCK_NB);
        if (FALSE === $unlocked) {
            trigger_error("не вдось розблокувати pid-файл.");
        }
    }

    /**
     * @param $pidFileResource
     *
     * @throws \Exception
     */
    private function closePidFile($pidFileResource)
    {
        $closed = fclose($pidFileResource);
        if (FALSE === $closed) {
            throw new \Exception('File can not close');
        }
    }

    /**
     * Відключає встановлену заборону паралельного запуска у яких спільний $pidFilePath
     *
     * @param string $pidFilePath
     *
     * @throws \Exception
     */
    public function deletePidFile($pidFilePath)
    {
        $deleted = unlink($pidFilePath);
        if (FALSE === $deleted) {
            if (is_file($pidFilePath)) {
                throw new \Exception('Видалити pid-файл не вдалось.');
            }
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
    public function errorHandle(int $messageType, string $messageText, string $messageFile, int $messageLine)
    {
        // добавляємо лише інформацію яка є.
        // все інше добавляти має обробник самого проекта.
        $message = "[$messageType] $messageText in $messageFile on line $messageLine";

        $this->lastError = new \Exception($message);

        // Перехопити перехопили, кидаєм далі обробляти.
        return false;
    }


}

