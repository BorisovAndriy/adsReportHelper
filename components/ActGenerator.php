<?php

namespace app\components;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use app\components\SpreadsheetToPdf;
use yii\base\Component;
use yii\base\Exception;
use app\components\NumberToWords;

/**
 * Клас для генерації акту надання послуг із динамічними даними та подальшою конвертацією в PDF.
 *
 * Автор: Андрій Борисов
 */
class ActGenerator extends Component
{
    /**
     * Генерує акт надання послуг з переданими параметрами і зберігає його в XLSX та PDF форматах.
     *
     * @param float $totalSumma Загальна сума акту.
     * @param string $periodFrom Дата початку періоду у форматі 'd.m.Y H:i:s'.
     * @param string $periodTo Дата закінчення періоду у форматі 'd.m.Y H:i:s'.
     * @param int $docsNumber Номер документа (акту).
     * @return void
     * @throws Exception Якщо виникають помилки під час обробки файлів.
     */
    public function generateAct(float $totalSumma, string $periodFrom, string $periodTo, int $docsNumber): string
    {
        // Конвертація числа у слова (сума прописом)
        $numberToWords = new NumberToWords();
        $totalSummaInWords = $numberToWords->convert($totalSumma);

        // Робота з датами
        $dateHandler = new DateHandler();
        $dateFrom = $dateHandler->getDateFromDateTime($periodFrom);
        $dateTo = $dateHandler->getDateFromDateTime($periodTo);

        // Формування даних для акту
        $actData = $this->prepareActData($docsNumber, $totalSumma, $totalSummaInWords, $dateFrom, $dateTo);

        try {
            // Обробка Excel-шаблону
            $inputFileName = 'source/act_template.xlsx';
            $spreadsheet = IOFactory::load($inputFileName);
            $sheet = $spreadsheet->getActiveSheet();

            //$sheet->setCellValue('C7', 'Слава Україні');

            // Заповнення акту даними
            foreach ($actData as $key => $value) {

               $sheet->setCellValue($key, $value);
            }

            // Збереження XLSX файлу
            $outputActFileName = 'source/' . $docsNumber . '_act_andriy_borisov.xlsx';
           // echo '<pre>';
           // var_dump($spreadsheet->getActiveSheet());
            //die();
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save($inputFileName);

//            echo 'Файл акту успішно створено!<br>';
        } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
            throw new Exception('Помилка зчитування файлу: ' . $e->getMessage());
        } catch (\PhpOffice\PhpSpreadsheet\Writer\Exception $e) {
            throw new Exception('Помилка збереження файлу: ' . $e->getMessage());
        }

        // Копіювання файлу з новим ім'ям
        $this->handleFileCopy($inputFileName, $outputActFileName);

        return $outputActFileName;
        // Конвертація файлу в PDF
        $outputActPdfFileName = 'source/' . $docsNumber . '_act_andriy_borisov.pdf';
//        $this->convertToPdf($outputActFileName, $outputActPdfFileName);
    }

    /**
     * Підготовка даних для заповнення Excel шаблону.
     *
     * @param int $docsNumber Номер документа.
     * @param float $totalSumma Загальна сума акту.
     * @param string $totalSummaInWords Загальна сума прописом.
     * @param string $dateFrom Дата початку періоду.
     * @param string $dateTo Дата закінчення періоду.
     * @return array Повертає масив із заповненими даними для акту.
     */
    private function prepareActData(int $docsNumber, float $totalSumma, string $totalSummaInWords, string $dateFrom, string $dateTo): array
    {
        return [
            'C7' => 'АКТ надання послуг  № ' . $docsNumber . ' від ' . $dateTo,
            'T12' => 'Рахунок на оплату покупцю  № ' . $docsNumber . ' від ' . $dateTo,
            'F17' => 'Послуги з лідогенерації по розміщенню рекламних матеріалів в мережі інтернет за період (' . $dateFrom . '-' . $dateTo . ')',
            'C32' => 'Дата ' . $dateTo,
            'AJ32' => 'Дата ' . $dateTo,
            'BG17' => $totalSumma,
            'BM17' => $totalSumma,
            'BM19' => $totalSumma,
            'BM21' => $totalSumma,
            'C23' => 'Загальна вартість робіт (послуг) склала без ПДВ ' . $totalSummaInWords . ', ПДВ нуль гривень 00 копійок, загальна вартість робіт (послуг) із ПДВ ' . $totalSummaInWords,
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
    private function handleFileCopy(string $inputFileName, string $outputFileName): void
    {
        $fileHandler = new FileHandler();

        try {
            $fileHandler->copyFileWithNewName($inputFileName, $outputFileName);
//            echo "Файл успішно скопійовано!<br>";
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
//            echo "Конвертація у PDF пройшла успішно!<br>";
        } catch (\Exception $e) {
            throw new Exception("Помилка конвертації у PDF: " . $e->getMessage());
        }
    }
}

