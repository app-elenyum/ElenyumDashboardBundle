<?php

namespace Elenyum\Dashboard\Service\Doc;

use DateTime;
use Exception;
use Generator;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\Yaml\Yaml;
use Symfony\Contracts\Service\Attribute\Required;

abstract class AbstractModuleService implements DocInterface
{
    /**
     * @var string|null
     */
    private ?string $path;

    /**
     * @var string|null
     */
    private ?string $namespace;

    #[Required]
    public KernelInterface $kernel;

    #[Required]
    public function setOptions(#[Autowire('%elenyum_dashboard.config%')] $options): void
    {
        $root = $options['root'] ?? null;
        if ($root === null) {
            throw new MissingOptionsException('Not defined "root" option');
        }

        $this->path = $root['path'] ?? null;
        if ($this->path === null) {
            throw new MissingOptionsException('Not defined "path" option');
        }

        $this->namespace = $root['namespace'] ?? null;
        if ($this->namespace === null) {
            throw new MissingOptionsException('Not defined "namespace" option');
        }
    }

    protected function parseConfig(string $file): ?array
    {
        $file = $this->path.'/../config/'.$file;
        if (file_exists($file)) {
            return Yaml::parseFile($file);
        }

        return null;
    }

    /**
     * @throws Exception
     */
    protected function getControllerStatsLog(): Generator
    {
        $logDir = $this->kernel->getLogDir().'/elenyum/';
        $logPattern = '/^controller_stats-\d{4}-\d{2}-\d{2}\.log$/'; // Регулярное выражение для имени файла

        // Получаем список всех файлов в директории
        $logFiles = scandir($logDir);

        if (!$logFiles) {
            throw new Exception("Unable to read log directory: $logDir");
        }

        foreach ($logFiles as $file) {
            if (preg_match($logPattern, $file)) { // Проверяем, подходит ли имя файла под шаблон
                $filePath = $logDir . $file;
                $fileHandle = fopen($filePath, 'r');

                if ($fileHandle) {
                    try {
                        while (($line = fgets($fileHandle)) !== false) {
                            yield $this->parseLogLine($line); // Парсим строку и возвращаем результат
                        }
                    } finally {
                        fclose($fileHandle);
                    }
                }
            }
        }
    }

    /**
     * @param string $line
     * @return ControllerStatsLogObject|null
     * @throws Exception
     */
    private function parseLogLine(string $line): ?ControllerStatsLogObject
    {
        // Регулярное выражение для успешных запросов
        $successPattern = '/Controller executed\. \{"timestamp":"(?P<timestamp>[^"]+)",'
            . '"method":"(?P<method>[^"]+)",'
            . '"endpoint":"(?P<endpoint>[^"]+)",'
            . '"controller":"(?P<controller>[^"]+)",'
            . '"duration":(?P<duration>\d+(\.\d+)?)\}/';

        // Регулярное выражение для запросов с ошибками
        $errorPattern = '/Controller execution failed\. \{"timestamp":"(?P<timestamp>[^"]+)",'
            . '"method":"(?P<method>[^"]+)",'
            . '"endpoint":"(?P<endpoint>[^"]+)",'
            . '"controller":"(?P<controller>[^"]+)",'
            . '"duration":(?P<duration>\d+(\.\d+)?)?,'
            . '"error_message":"(?P<error_message>[^"]+)",'
            . '"error_code":(?P<error_code>\d+)\}/';

        // Проверяем, соответствует ли строка формату успешного запроса
        if (preg_match($successPattern, $line, $matches)) {
            // Преобразуем timestamp в объект DateTime
            $timestamp = \DateTime::createFromFormat('Y-m-d H:i:s', $matches['timestamp']);

            if ($timestamp === false) {
                throw new Exception('Invalid timestamp format');
            }

            return new ControllerStatsLogObject(
                $timestamp,
                $matches['method'],
                $matches['endpoint'],
                $matches['controller'],
                (int)$matches['duration'],
                ControllerStatsLogObject::STATUS_SUCCESS,
                ControllerStatsLogObject::TYPE_REQUEST,
            );
        }

        // Проверяем, соответствует ли строка формату запроса с ошибкой
        if (preg_match($errorPattern, $line, $matches)) {
            // Преобразуем timestamp в объект DateTime
            $timestamp = DateTime::createFromFormat('Y-m-d H:i:s', $matches['timestamp']);

            if ($timestamp === false) {
                throw new Exception('Invalid timestamp format');
            }

            return new ControllerStatsLogObject(
                $timestamp,
                $matches['method'],
                $matches['endpoint'],
                $matches['controller'],
                isset($matches['duration']) ? (int)$matches['duration'] : null,
                ControllerStatsLogObject::STATUS_ERROR,
                ControllerStatsLogObject::TYPE_EXCEPTION,  // Ошибка будет иметь тип 'exception'
                $matches['error_message'],
                (int)$matches['error_code'],
            );
        }

        // Вернем null, если строка не соответствует ни одному из форматов
        throw new Exception('Invalid log format');
    }

    abstract public function getStats(): array;
}