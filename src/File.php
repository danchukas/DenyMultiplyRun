<?php
declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: danchukas
 * Date: 2017-07-24 14:08
 */

namespace DanchukAS\DenyMultiplyRun;

use DanchukAS\DenyMultiplyRun\Exception\CloseFileFail;
use DanchukAS\DenyMultiplyRun\Exception\DeleteFileFail;
use DanchukAS\DenyMultiplyRun\Exception\FileExisted;
use DanchukAS\DenyMultiplyRun\Exception\LockFileFail;
use DanchukAS\DenyMultiplyRun\Exception\OpenFileFail;

/**
 * Class File
 * @package DanchukAS\DenyMultiplyRun
 */
class File
{
    /**
     * @param $file_resource
     *
     * @throws \Exception
     */
    public static function lockPidFile($file_resource)
    {
        $locked = flock($file_resource, LOCK_EX | LOCK_NB);

        if (false === $locked) {
            $error = ErrorHandler::$lastError;

            // перехоплювач на 1 команду, щоб в разі потреби потім дізнатись причину несправності.
            // помилку в записує в ErrorHandler::$lastError
            ErrorHandler::startErrorHandle();

            // собачка потрібна щоб не засоряти логи.
            /** @noinspection PhpUsageOfSilenceOperatorInspection */
            $resource_data = @stream_get_meta_data($file_resource);

            // Відновлюєм попередній обробник наче нічого і не робили.
            restore_error_handler();

            throw new LockFileFail($resource_data['uri'] . ' - ' . $error);
        }
    }

    /**
     * @param $file_resource
     * @throws \DanchukAS\DenyMultiplyRun\Exception\CloseFileFail
     */
    public static function safeCloseFile($file_resource)
    {
        try {
            self::unlockFile($file_resource);
        } finally {
            self::closeFile($file_resource);
        }
    }

    /**
     * @param $pidFileResource
     */
    public static function unlockFile($pidFileResource)
    {
        $unlocked = flock($pidFileResource, LOCK_UN | LOCK_NB);
        if (false === $unlocked) {
            trigger_error('не вдось розблокувати pid-файл.');
        }
    }


    /**
     * @param $pidFileResource
     *
     * @throws CloseFileFail
     */
    public static function closeFile($pidFileResource)
    {
        // перехоплювач на 1 команду, щоб в разі потреби потім дізнатись причину несправності.
        // помилку в записує в ErrorHandler::$lastError
        ErrorHandler::startErrorHandle();

        try {
            // собачка потрібна щоб не засоряти логи.
            /** @noinspection PhpUsageOfSilenceOperatorInspection */
            $closed = @fclose($pidFileResource);
        } catch (\Throwable $error) {
            $closed = false;
            ErrorHandler::$lastError = $error;
        } finally {
            // Відновлюєм попередній обробник наче нічого і не робили.
            restore_error_handler();
        }

        if (false === $closed) {
            self::closeFileFailed($pidFileResource);
        }
    }


    /**
     * @param $pidFileResource
     * @throws CloseFileFail
     */
    private static function closeFileFailed($pidFileResource)
    {
        $file_close_error = ErrorHandler::$lastError;

        // перехоплювач на 1 команду, щоб в разі потреби потім дізнатись причину несправності.
        // помилку в записує в ErrorHandler::$lastError
        ErrorHandler::startErrorHandle();

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
     * @param string $filePath
     *
     * @throws DeleteFileFail
     */
    public static function deleteFile($filePath)
    {
        // перехоплювач на 1 команду, щоб в разі потреби потім дізнатись причину несправності.
        // помилку в записує в ErrorHandler::$lastError
        ErrorHandler::startErrorHandle();

        try {
            // собачка потрібна щоб не засоряти логи.
            /** @noinspection PhpUsageOfSilenceOperatorInspection */
            @unlink($filePath);
        } catch (\Throwable $error) {
            ErrorHandler::$lastError = $error;
        } finally {
            // Відновлюєм попередній обробник наче нічого і не робили.
            restore_error_handler();
        }

        if (null !== ErrorHandler::$lastError) {
            throw new DeleteFileFail(ErrorHandler::$lastError);
        }
    }


    /**
     * @param string $pidFilePath
     * @param string $mode
     * @return resource
     */
    public static function openFile($pidFilePath, string $mode)
    {
        ErrorHandler::startErrorHandle();
        try {
            /** @noinspection PhpUsageOfSilenceOperatorInspection */
            $pid_file_handle = @fopen($pidFilePath, $mode);
        } catch (\Throwable $error) {
            ErrorHandler::$lastError = $error;
        } finally {
            restore_error_handler();
        }

        if (null !== ErrorHandler::$lastError) {
            throw new OpenFileFail((string)ErrorHandler::$lastError);
        }

        /** @noinspection PhpUndefinedVariableInspection */
        return $pid_file_handle;
    }


    /**
     * @param string $filePath
     *
     * @throws \Exception
     */
    public static function prepareDir($filePath)
    {
        $dir = \dirname($filePath);

        /** @noinspection MkdirRaceConditionInspection */
        if ('' !== $dir
            && !\is_dir($dir)
            && !\mkdir($dir, 0777, true)
        ) {
            throw new \RuntimeException('Директорія відсутня і неможливо створити: ' . $dir);
        }
    }


    /**
     * @param string $pidFilePath
     * @return resource
     * @throws \Throwable
     * @throws FileExisted
     * @throws \Exception
     */
    public static function createPidFile($pidFilePath)
    {
        // перехоплювач на 1 команду, щоб в разі потреби потім дізнатись причину несправності.
        // помилку в записує в ErrorHandler::$lastError
        ErrorHandler::startErrorHandle();

        // собачка потрібна щоб не засоряти логи.
        /** @noinspection PhpUsageOfSilenceOperatorInspection */
        $pid_file_handle = @fopen($pidFilePath, 'xb');

        // Відновлюєм попередній обробник наче нічого і не робили.
        restore_error_handler();

        // файл не створений. сталась помилка
        if (null !== ErrorHandler::$lastError) {
            self::createPidFileFailed($pidFilePath);
        }

        // файл створений успішно.
        return $pid_file_handle;
    }


    /**
     * @param $pidFilePath
     * @throws FileExisted
     * @throws \Throwable
     */
    private static function createPidFileFailed($pidFilePath)
    {
        // Файла і нема і не створився - повідомляєм про несправність проекта.
        if (!\is_file($pidFilePath)) {
            throw ErrorHandler::$lastError;
        }

        // Файл вже існує, тому не створився.
        throw new FileExisted($pidFilePath);
    }


    /**
     * @param $fileResource
     *
     * @throws \Exception
     */
    public static function truncateFile($fileResource)
    {
        $truncated = ftruncate($fileResource, 0);
        if (false === $truncated) {
            throw new \RuntimeException('не вдалось очистити pid-файл.');
        }

        $cursor_to_begin = rewind($fileResource);
        if (!$cursor_to_begin) {
            throw new \RuntimeException('не вдалось перемістити курсор на початок pid-файла.');
        }
    }

}