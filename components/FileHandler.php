<?php

namespace app\components;

use yii\base\Component;
use yii\base\Exception;
use ZipArchive;

/**
 * Клас FileHandler призначений для роботи з файлами, зокрема копіювання файлів з новим іменем і створення ZIP-архівів.
 */
class FileHandler extends Component
{
    /**
     * Метод для копіювання файлу з новим іменем.
     *
     * @param string $sourceFile Повний шлях до оригінального файлу.
     * @param string $destinationFile Повний шлях до нового файлу з новим ім'ям.
     * @return bool Повертає true, якщо копіювання успішне, інакше кидає виключення.
     * @throws Exception Якщо файл не існує або копіювання не вдалося.
     */
    public function copyFileWithNewName(string $sourceFile, string $destinationFile): bool
    {
        // Перевіряємо, чи існує оригінальний файл
        if (!file_exists($sourceFile)) {
            throw new Exception("Файл $sourceFile не існує.");
        }

        // Виконуємо копіювання файлу
        if (!copy($sourceFile, $destinationFile)) {
            throw new Exception("Не вдалося скопіювати файл до $destinationFile.");
        }

        return true;
    }

    /**
     * Метод для створення ZIP-архіву з кількох файлів.
     *
     * @param array $files Масив шляхів до файлів, які необхідно додати до архіву.
     * @param string $zipFileName Повний шлях до ZIP-файлу, який буде створено.
     * @return bool Повертає true, якщо архів успішно створено, інакше кидає виключення.
     * @throws Exception Якщо архів не вдалося створити або якщо додавання файлів до архіву не вдалося.
     */
    public function createZipArchive(array $files, string $zipFileName): bool
    {
        // Ініціалізація ZipArchive
        $zip = new ZipArchive();

        // Спроба відкрити або створити ZIP-файл
        if ($zip->open($zipFileName, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
            throw new Exception("Не вдається створити або відкрити ZIP-файл: $zipFileName.");
        }

        // Додаємо кожен файл до архіву
        foreach ($files as $file) {
            if (file_exists($file)) {
                $zip->addFile($file, basename($file)); // Додаємо файл до архіву з його базовим ім'ям
            } else {
                throw new Exception("Файл $file не існує.");
            }
        }

        // Закриваємо архів
        $zip->close();

        // Перевіряємо, чи архів успішно створено
        if (!file_exists($zipFileName)) {
            throw new Exception("Не вдалося створити ZIP-файл: $zipFileName.");
        }

        return true;
    }

    /**
     * Метод для відправки ZIP-файлу користувачу для завантаження.
     *
     * @param string $zipFileName Повний шлях до ZIP-файлу, який необхідно завантажити.
     * @throws Exception Якщо файл не знайдено або не вдалося його надіслати.
     */
    public function sendZipForDownload(string $zipFileName): void
    {
        // Перевіряємо, чи існує ZIP-файл
        if (!file_exists($zipFileName)) {
            throw new Exception("ZIP-файл $zipFileName не існує.");
        }

        // Відправляємо заголовки для завантаження файлу
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . basename($zipFileName) . '"');
        header('Content-Length: ' . filesize($zipFileName));

        // Виводимо вміст файлу
        readfile($zipFileName);

        // Видаляємо ZIP-файл після відправки
        unlink($zipFileName);

        exit(); // Завершуємо виконання скрипта після відправки файлу
    }
}
