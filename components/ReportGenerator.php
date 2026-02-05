<?php

namespace app\components;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use app\components\SpreadsheetToPdf;
use yii\base\Component;
use yii\base\Exception;
use app\components\NumberToWords;

/**
 * Клас для генерації звіту із динамічними даними та подальшою конвертацією в PDF.
 *
 * Автор: Андрій Борисов
 */
class ReportGenerator extends Component
{
    /**
     * Генерує звіт з переданими параметрами і зберігає його в XLSX та PDF форматах.
     *
     * @param float $totalSum Загальна сума по звіту.
     * @param string $periodFrom Дата початку періоду у форматі 'd.m.Y H:i:s'.
     * @param string $periodTo Дата закінчення періоду у форматі 'd.m.Y H:i:s'.
     * @param int $docsNumber Номер документа (звіту).
     * @param array $datas Масив даних для заповнення таблиці.
     * @return string Шлях до згенерованого файлу.
     * @throws Exception Якщо виникають помилки під час обробки файлів.
     */
    public function generateReport(float $totalSum, string $periodFrom, string $periodTo, int $docsNumber, array $datas): string
    {
        // Конвертація числа у слова
        $numberToWords = new NumberToWords();
        $totalSumInWords = $numberToWords->convert($totalSum);

        // Робота з датами
        $dateHandler = new DateHandler();
        $dateFrom = $dateHandler->getDateFromDateTime($periodFrom);
        $dateTo = $dateHandler->getDateFromDateTime($periodTo);

        // Формування метаданих для звіту
        $reportData = $this->prepareReportData($docsNumber, $totalSum, $totalSumInWords, $dateFrom, $dateTo);

        try {
            $inputFileName = 'source/report_template.xlsx';
            $spreadsheet = IOFactory::load($inputFileName);
            $sheet = $spreadsheet->getActiveSheet();

            // Очищення масиву від заголовків
            unset($datas[0], $datas[1]);

            // Початковий рядок для вставки даних (зазвичай після заголовків таблиці)
            $startRow = 3;
            $currentRow = $startRow;

            if (!empty($datas)) {
                foreach ($datas as $data) {
                    // Вставляємо новий рядок. Всі рядки шаблону, що нижче, автоматично зсуваються.
                    $sheet->insertNewRowBefore($currentRow, 1);

                    // Генерація випадкових дат для звіту
                    $randomDateAction = $dateHandler->generateRandomDateTimeBetween($periodFrom, $periodTo);
                    $randomDateProcessing = $dateHandler->generateRandomDateTimeFromPastThreeMonths($periodFrom);

                    // Заповнення комірок
                    $sheet->setCellValue('A' . $currentRow, $data[0]);  // Admitad ID
                    $sheet->setCellValue('B' . $currentRow, $randomDateProcessing); // Час обробки
                    $sheet->setCellValue('C' . $currentRow, $randomDateAction);     // Час дії
                    $sheet->setCellValue('D' . $currentRow, $data[5]);             // Сума винагороди

                    $currentRow++;
                }
            }

            // ПІДСУМКИ:
            // Після циклу $currentRow вказує на рядок, де в шаблоні зазвичай "Сума без ПДВ"
            $lastDataRow = $currentRow - 1;
            $sumRange = "D{$startRow}:D{$lastDataRow}";

            // Формула суми (тепер динамічна)
            $sumFormula = "=SUM({$sumRange})";

            // Записуємо суму в три стандартні рядки шаблону (Сума без ПДВ, ПДВ, Разом)
            $sheet->setCellValue('D' . $currentRow, $sumFormula);       // Сума без ПДВ
            $sheet->setCellValue('D' . ($currentRow + 1), 0);          // ПДВ (0 для ФОП не платника)
            $sheet->setCellValue('D' . ($currentRow + 2), $sumFormula); // Разом з ПДВ

            // Заповнення метаданих (Заголовок в A1 тощо)
            foreach ($reportData as $key => $value) {
                $sheet->setCellValue($key, $value);
            }

            // Збереження результату
            $outputReportFileName = 'source/' . $docsNumber . '_report_andriy_borisov.xlsx';
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save($outputReportFileName);

        } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
            throw new Exception('Помилка зчитування шаблону: ' . $e->getMessage());
        } catch (\PhpOffice\PhpSpreadsheet\Writer\Exception $e) {
            throw new Exception('Помилка збереження звіту: ' . $e->getMessage());
        }

        return $outputReportFileName;
    }

    /**
     * Підготовка даних для заповнення Excel шаблону.
     */
    private function prepareReportData(int $docsNumber, float $totalSum, string $totalSumInWords, string $dateFrom, string $dateTo): array
    {
        return [
            'A1' => 'Admitad звіт ФОП Борисов Андрій до акту № ' . $docsNumber . ' від ' . $dateTo,
        ];
    }

    /**
     * Копіює файл з новим ім'ям.
     */
    private function copyFile(string $inputFileName, string $outputFileName): void
    {
        $fileHandler = new FileHandler();
        try {
            $fileHandler->copyFileWithNewName($inputFileName, $outputFileName);
        } catch (\Exception $e) {
            throw new Exception("Помилка копіювання файлу: " . $e->getMessage());
        }
    }

    /**
     * Конвертує файл XLSX у PDF.
     */
    private function convertToPdf(string $xlsxFile, string $pdfFile): void
    {
        $converter = new SpreadsheetToPdf();
        try {
            $converter->convertXlsxToPdf($xlsxFile, $pdfFile);
        } catch (\Exception $e) {
            throw new Exception("Помилка конвертації у PDF: " . $e->getMessage());
        }
    }
}