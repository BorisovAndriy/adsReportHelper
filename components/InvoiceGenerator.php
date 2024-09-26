<?php
namespace app\components;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use app\components\SpreadsheetToPdf;
use yii\base\Component;
use yii\base\Exception;
use app\components\NumberToWords;

/**
 * Клас для генерації рахунку надання послуг із динамічними даними та подальшою конвертацією в PDF.
 *
 * Автор: Андрій Борисов
 */
class InvoiceGenerator extends Component
{
    /**
     * Генерує рахунок надання послуг з переданими параметрами і зберігає його в XLSX та PDF форматах.
     *
     * @param float $totalSum Загальна сума рахунку.
     * @param string $periodFrom Дата початку періоду у форматі 'd.m.Y H:i:s'.
     * @param string $periodTo Дата закінчення періоду у форматі 'd.m.Y H:i:s'.
     * @param int $docsNumber Номер документа (рахунку).
     * @return void
     * @throws Exception Якщо виникають помилки під час обробки файлів.
     */
    public function generateInvoice(float $totalSum, string $periodFrom, string $periodTo, int $docsNumber): string
    {
        // Конвертація числа у слова (сума прописом)
        $numberToWords = new NumberToWords();
        $totalSumInWords = $numberToWords->convert($totalSum);

        // Робота з датами
        $dateHandler = new DateHandler();
        $dateFrom = $dateHandler->getDateFromDateTime($periodFrom);
        $dateTo = $dateHandler->getDateFromDateTime($periodTo);

        // Формування даних для рахунку
        $invoiceData = $this->prepareInvoiceData($docsNumber, $totalSum, $totalSumInWords, $dateFrom, $dateTo);

        try {
            // Обробка Excel-шаблону
            $inputFileName = 'source/invoice_template.xlsx';
            $spreadsheet = IOFactory::load($inputFileName);
            $sheet = $spreadsheet->getActiveSheet();

            // Заповнення рахунку даними
            foreach ($invoiceData as $key => $value) {
                $sheet->setCellValue($key, $value);
            }

            // Збереження XLSX файлу
            $outputInvoiceFileName = 'source/' . $docsNumber . '_invoice_andriy_borisov.xlsx';
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save($inputFileName);

        } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
            throw new Exception('Помилка зчитування файлу: ' . $e->getMessage());
        } catch (\PhpOffice\PhpSpreadsheet\Writer\Exception $e) {
            throw new Exception('Помилка збереження файлу: ' . $e->getMessage());
        }

        // Копіювання файлу з новим ім'ям
        $this->copyFile($inputFileName, $outputInvoiceFileName);
        return $outputInvoiceFileName;

        // Конвертація файлу в PDF
        //$outputInvoicePdfFileName = 'source/' . $docsNumber . '_invoice_andriy_borisov.pdf';
    }

    /**
     * Підготовка даних для заповнення Excel шаблону.
     *
     * @param int $docsNumber Номер документа.
     * @param float $totalSum Загальна сума рахунку.
     * @param string $totalSumInWords Загальна сума прописом.
     * @param string $dateFrom Дата початку періоду.
     * @param string $dateTo Дата закінчення періоду.
     * @return array Повертає масив із заповненими даними для рахунку.
     */
    private function prepareInvoiceData(int $docsNumber, float $totalSum, string $totalSumInWords, string $dateFrom, string $dateTo): array
    {
        return [
            'C17' => 'Рахунок на оплату послуг № ' . $docsNumber . ' від ' . $dateTo,
            //'T12' => 'Рахунок покупцю № ' . $docsNumber . ' від ' . $dateTo,
            'E27' => 'Послуги з лідогенерації по розміщенню рекламних матеріалів в мережі інтернет за період  (' . $dateFrom . '-' . $dateTo . ')',
            //'C32' => 'Дата ' . $dateTo,
            //'AJ32' => 'Дата ' . $dateTo,
            'AE27' => $totalSum,
            'AI27' => $totalSum,
            'AI29' => $totalSum,
            'AJ31' => $totalSum,
            'C34' => $totalSumInWords . '. У т.ч. ПДВ: нуль гривень нуль копійок (не платник ПДВ)',
            //П'ятнадцять тисяч сто сорок сім гривень, 58 копійок. У т.ч. ПДВ: нуль гривень нуль копійок (не платник ПДВ)
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
