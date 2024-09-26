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
     * @return void
     * @throws Exception Якщо виникають помилки під час обробки файлів.
     */
    public function generateReport(float $totalSum, string $periodFrom, string $periodTo, int $docsNumber, array $datas): string
    {
        // Конвертація числа у слова (сума прописом)
        $numberToWords = new NumberToWords();
        $totalSumInWords = $numberToWords->convert($totalSum);

        // Робота з датами
        $dateHandler = new DateHandler();
        $dateFrom = $dateHandler->getDateFromDateTime($periodFrom);
        $dateTo = $dateHandler->getDateFromDateTime($periodTo);

        // Формування даних для звіту
        $reportData = $this->prepareReportData($docsNumber, $totalSum, $totalSumInWords, $dateFrom, $dateTo);

        try {
            // Обробка Excel-шаблону
            $inputFileName = 'source/report_template.xlsx';
            $spreadsheet = IOFactory::load($inputFileName);
            $sheet = $spreadsheet->getActiveSheet();


/*
echo '<pre>';
            var_dump($datas);;
            die();
*/
            unset($datas[0]);
            unset($datas[1]);
            if (!empty($datas)) {
                foreach ($datas as $key => $data) {
                    // Вставка нового рядка перед четвертим (старий 4-й рядок стає 5-м)
                    $sheet->insertNewRowBefore($key + 1, 1); // Вставляємо один новий рядок на позиції $key+4


                    // Формування адрес клітинок для рядка $key+4
                    $sheet->setCellValue('A' . ($key + 1), $data[0]);  // Значення для стовпця A

                    $randomDate = $dateHandler->generateRandomDateTimeBetween($periodFrom, $periodTo);
                    $randomDate2 = $dateHandler->generateRandomDateTimeFromPastThreeMonths($periodFrom);

                    $sheet->setCellValue('B' . ($key + 1),  $randomDate2);  // Значення для стовпця B
                    $sheet->setCellValue('C' . ($key + 1),  $randomDate);  // Значення для стовпця C
                    $sheet->setCellValue('D' . ($key + 1), $data[5]);  // Значення для стовпця D
                }
            }

            $sheet->setCellValue('D278', '=SUM(D3:D277)');  // Значення для стовпця D
            //=SUM(D285:D287)
// Вставляємо дані в новий рядок


            // Заповнення звіту даними

            foreach ($reportData as $key => $value) {
                $sheet->setCellValue($key, $value);
            }


            // Збереження XLSX файлу
            $outputReportFileName = 'source/' . $docsNumber . '_report_andriy_borisov.xlsx';
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save($outputReportFileName);

        } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
            throw new Exception('Помилка зчитування файлу: ' . $e->getMessage());
        } catch (\PhpOffice\PhpSpreadsheet\Writer\Exception $e) {
            throw new Exception('Помилка збереження файлу: ' . $e->getMessage());
        }

        // Копіювання файлу з новим ім'ям
        //$this->copyFile($inputFileName, $outputReportFileName);
        return $outputReportFileName;
    }

    /**
     * Підготовка даних для заповнення Excel шаблону.
     *
     * @param int $docsNumber Номер документа.
     * @param float $totalSum Загальна сума по звіту.
     * @param string $totalSumInWords Загальна сума прописом.
     * @param string $dateFrom Дата початку періоду.
     * @param string $dateTo Дата закінчення періоду.
     * @return array Повертає масив із заповненими даними для звіту.
     */
    private function prepareReportData(int $docsNumber, float $totalSum, string $totalSumInWords, string $dateFrom, string $dateTo): array
    {
        return [
            'A1' => 'Admitad звіт ФОП Борисов Андрій до акту № ' . $docsNumber . ' від ' . $dateTo,
            //'E27' => 'Послуги за період (' . $dateFrom . ' - ' . $dateTo . ')',
            //'AE27' => $totalSum,
            //'AI27' => $totalSum,
            //'AI29' => $totalSum,
            //'AJ31' => $totalSum,
            //'C34' => $totalSumInWords . '. У т.ч. ПДВ: нуль гривень нуль копійок (не платник ПДВ)',
        ];
    }

    /**
     * Копіює файл з новим ім'ям.
     *
     * @param string $inputFileName Шлях до оригінального файлу.
     * @param string $outputFileName Шлях до нового файлу.
     * @return void
     * @throws Exception У випадку помилки під час копіювання.
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
     *
     * @param string $xlsxFile Шлях до XLSX файлу.
     * @param string $pdfFile Шлях до PDF файлу.
     * @return void
     * @throws Exception У випадку помилки конвертації.
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
