<?php

namespace app\components;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Mpdf\Mpdf;
use yii\base\Component;
use yii\base\Exception;

/**
 * Клас для конвертації файлів XLSX у PDF з можливістю видалення зайвого контенту.
 *
 * Автор: Андрій Борисов
 */
class SpreadsheetToPdf extends Component
{
    /**
     * Конвертує файл XLSX у PDF, видаляючи зайвий контент на основі фраз.
     *
     * Видаляється 30 символів до фрази "АКТ надання послуг" і 30 символів після фрази "Платник ПДВ на загальних підставах.".
     *
     * @param string $xlsxFile Шлях до файлу XLSX.
     * @param string $pdfFile Шлях до вихідного файлу PDF.
     * @return bool Повертає true у разі успішної конвертації.
     * @throws Exception У випадку, якщо файл XLSX не існує або виникає помилка під час обробки.
     */
    public function convertXlsxToPdf(string $xlsxFile, string $pdfFile): bool
    {
        set_time_limit(300); // збільшуємо час виконання до 5 хвилин

        if (!file_exists($xlsxFile)) {
            throw new Exception("Файл $xlsxFile не існує.");
        }

        // Завантажуємо файл XLSX
        $spreadsheet = IOFactory::load($xlsxFile);

        // Конвертуємо його в HTML
        $writer = IOFactory::createWriter($spreadsheet, 'Html');
        ob_start();
        $writer->save('php://output');
        $htmlContent = ob_get_clean();

        // Видаляємо 30 символів до фрази "АКТ надання послуг" і 30 символів після фрази "Платник ПДВ на загальних підставах."
        $htmlContent = $this->extractContentAroundPhrases(
            $htmlContent,
            'АКТ надання послуг',
            'Платник ПДВ на загальних підставах.',
            300 // кількість символів до і після фрази для видалення
        );

        // Створюємо новий об'єкт Mpdf
        $mpdf = new Mpdf();

        // Пишемо HTML до PDF по частинах
        $this->writeHtmlInChunks($mpdf, $htmlContent);

        // Генеруємо PDF файл
        $mpdf->Output($pdfFile, 'F');

        return true;
    }

    /**
     * Пише HTML контент до PDF файлу по частинах для уникнення перевантаження пам'яті.
     *
     * @param Mpdf $mpdf Об'єкт Mpdf для запису PDF.
     * @param string $htmlContent HTML контент, який необхідно записати в PDF.
     * @param int $chunkSize Розмір частини, за замовчуванням 10000 символів.
     */
    private function writeHtmlInChunks(Mpdf $mpdf, string $htmlContent, int $chunkSize = 10000): void
    {
        $length = strlen($htmlContent);
        for ($start = 0; $start < $length; $start += $chunkSize) {
            $chunk = substr($htmlContent, $start, $chunkSize);
            $mpdf->WriteHTML($chunk);
        }
    }

    /**
     * Видаляє 30 символів до фрази та 30 символів після фрази, зберігаючи форматування HTML.
     *
     * @param string $htmlContent HTML контент, який потрібно обробити.
     * @param string $startPhrase Фраза, після якої потрібно почати витягувати контент.
     * @param string $endPhrase Фраза, перед якою потрібно закінчити витягувати контент.
     * @param int $bufferLength Кількість символів до і після фрази, які необхідно видалити.
     * @return string Оновлений HTML контент без зайвого контенту.
     */
    private function extractContentAroundPhrases(string $htmlContent, string $startPhrase, string $endPhrase, int $bufferLength = 30): string
    {
        // Знайдемо позицію початку контенту
        $startPos = strpos($htmlContent, $startPhrase);
        if ($startPos === false) {
            throw new Exception("Фразу '$startPhrase' не знайдено в HTML контенті.");
        }

        // Віднімаємо 30 символів перед фразою "АКТ надання послуг"
        $startPos = max(0, $startPos - $bufferLength);

        // Знайдемо позицію закінчення контенту
        $endPos = strpos($htmlContent, $endPhrase, $startPos);
        if ($endPos === false) {
            throw new Exception("Фразу '$endPhrase' не знайдено в HTML контенті.");
        }

        // Додаємо 30 символів після фрази "Платник ПДВ на загальних підставах."
        $endPos = min(strlen($htmlContent), $endPos + strlen($endPhrase) + $bufferLength);

        // Повертаємо вирізаний контент між двома фразами з буфером
        return substr($htmlContent, $startPos, $endPos - $startPos);
    }
}
