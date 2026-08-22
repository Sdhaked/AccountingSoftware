<?php

if (! function_exists('bootstrapEnsureAppKey')) {
    function bootstrapEnsureAppKey(string $basePath): void
    {
        $existingKey = $_ENV['APP_KEY'] ?? $_SERVER['APP_KEY'] ?? getenv('APP_KEY');

        if (is_string($existingKey) && trim($existingKey) !== '') {
            return;
        }

        $envPath = $basePath.'/.env';
        if (! is_file($envPath) || ! is_readable($envPath)) {
            return;
        }

        $envContents = file_get_contents($envPath);
        if ($envContents === false) {
            return;
        }

        $generatedKey = 'base64:'.base64_encode(random_bytes(32));
        $lines = preg_split("/\r\n|\n|\r/", $envContents) ?: [];
        $appKeyLineFound = false;

        foreach ($lines as $index => $line) {
            if (! str_starts_with($line, 'APP_KEY=')) {
                continue;
            }

            $appKeyLineFound = true;
            $currentValue = trim(substr($line, strlen('APP_KEY=')), " \t\n\r\0\x0B\"'");
            if ($currentValue !== '') {
                $_ENV['APP_KEY'] = $currentValue;
                $_SERVER['APP_KEY'] = $currentValue;

                return;
            }

            $lines[$index] = 'APP_KEY='.$generatedKey;
        }

        if (! $appKeyLineFound) {
            $lines[] = 'APP_KEY='.$generatedKey;
        }

        $updatedContents = implode(PHP_EOL, $lines);
        if (! str_ends_with($updatedContents, PHP_EOL)) {
            $updatedContents .= PHP_EOL;
        }

        if (file_put_contents($envPath, $updatedContents) === false) {
            return;
        }

        $_ENV['APP_KEY'] = $generatedKey;
        $_SERVER['APP_KEY'] = $generatedKey;
    }
}
