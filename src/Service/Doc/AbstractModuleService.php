<?php

namespace Elenyum\Dashboard\Service\Doc;

use DateTime;
use Elenyum\Maker\Service\Module\Config\ConfigEditorService;
use Exception;
use Generator;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
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

    /**
     * @var ConfigEditorService
     */
    private ConfigEditorService $config;

    #[Required]
    public KernelInterface $kernel;

    #[Required]
    public function setOptions(#[Autowire('%elenyum_dashboard.config%')] $options): void
    {

//        dd($options);
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
            $this->config = new ConfigEditorService($file);

            return $this->config->parse();
        }

        return null;
    }

    /**
     * @throws Exception
     */
    protected function getControllerStatsLog(): Generator
    {
        $logFile = $this->kernel->getLogDir().'/elenyum/controller_stats.log';

        if (file_exists($logFile)) {
            $file = fopen($logFile, 'r');

            if ($file) {
                try {
                    while (($line = fgets($file)) !== false) {
                        yield $this->parseLogLine($line);
                    }
                } finally {
                    fclose($file);
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
            . '"endpoint":"(?P<endpoint>[^"]+)",'
            . '"controller":"(?P<controller>[^"]+)",'
            . '"duration":(?P<duration>\d+(\.\d+)?)\}/';

        // Регулярное выражение для запросов с ошибками
        $errorPattern = '/Controller execution failed\. \{"timestamp":"(?P<timestamp>[^"]+)",'
            . '"endpoint":"(?P<endpoint>[^"]+)",'
            . '"controller":"(?P<controller>[^"]+)",'
            . '"duration":(?P<duration>\d+(\.\d+)?)?,'
            . '"error_message":"(?P<error_message>[^"]+)",'
            . '"error_code":"(?P<error_code>\d+)"\}/';

        // Проверяем, соответствует ли строка формату успешного запроса
        if (preg_match($successPattern, $line, $matches)) {
            // Преобразуем timestamp в объект DateTime
            $timestamp = \DateTime::createFromFormat('Y-m-d H:i:s', $matches['timestamp']);

            if ($timestamp === false) {
                throw new Exception('Invalid timestamp format');
            }

            return new ControllerStatsLogObject(
                $timestamp,
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
        return null;
    }

    abstract public function getStats(): array;
}