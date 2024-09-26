<?php
namespace app\components;
/**
 * Class NumberToWords
 * Клас для конвертації чисел в текстовий вигляд українською мовою.
 */
class NumberToWords {

    /**
     * Перетворює число з копійками у текстовий формат (гривні і копійки).
     *
     * @param float $number Число для перетворення.
     * @return string Текстове представлення числа.
     */
    public function convert($number) {
        // Форматуємо число для відокремлення гривень і копійок.
        $number = number_format($number, 2, '.', '');
        list($hryvnias, $kopecks) = explode('.', $number);

        $hryvnias = (int)$hryvnias;
        $kopecks = (int)$kopecks;

        $words = '';

        // Конвертуємо гривні у слова.
        if ($hryvnias > 0) {
            $words .= $this->convertPartToWords($hryvnias) . ' гривень';
        }

        // Конвертуємо копійки у слова.
        if ($kopecks > 0) {
            $words .= ' ' . $this->convertPartToWords($kopecks) . ' копійок';
        }

        return ucwords(trim($words));
    }

    /**
     * Перетворює частину числа (до 1000) в текстовий вигляд.
     *
     * @param int $number Число для перетворення.
     * @return string Текстове представлення числа.
     */
    private function convertPartToWords($number) {
        $units = ['', 'один', 'два', 'три', 'чотири', 'п\'ять', 'шість', 'сім', 'вісім', 'дев\'ять'];
        $teens = ['десять', 'одинадцять', 'дванадцять', 'тринадцять', 'чотирнадцять', 'п\'ятнадцять', 'шістнадцять', 'сімнадцять', 'вісімнадцять', 'дев\'ятнадцять'];
        $tens = ['', '', 'двадцять', 'тридцять', 'сорок', 'п\'ятдесят', 'шістдесят', 'сімдесят', 'вісімдесят', 'дев\'яносто'];
        $hundreds = ['', 'сто', 'двісті', 'триста', 'чотириста', 'п\'ятсот', 'шістсот', 'сімсот', 'вісімсот', 'дев\'ятсот'];

        $parts = [];

        // Перевіряємо тисячі і вище.
        if ($number >= 1000) {
            $thousands = (int)($number / 1000);
            $parts[] = $this->convertPartToWords($thousands) . ' ' . $this->getThousandsWord($thousands);
            $number %= 1000;
        }

        // Додаємо сотні, якщо є.
        if ($number >= 100) {
            $parts[] = $hundreds[(int)($number / 100)];
            $number %= 100;
        }

        // Додаємо десятки.
        if ($number >= 20) {
            $parts[] = $tens[(int)($number / 10)];
            $number %= 10;
        }

        // Обробляємо числа від 10 до 19.
        if ($number >= 10) {
            $parts[] = $teens[$number - 10];
        } elseif ($number > 0) {
            $parts[] = $units[$number];
        }

        return implode(' ', $parts);
    }

    /**
     * Вибирає правильну форму слова "тисяча" в залежності від числа.
     *
     * @param int $number Число тисяч.
     * @return string Правильна форма слова "тисяча".
     */
    private function getThousandsWord($number) {
        $lastTwoDigits = $number % 100;

        if ($lastTwoDigits >= 11 && $lastTwoDigits <= 19) {
            return 'тисяч';
        }

        $lastDigit = $number % 10;

        if ($lastDigit == 1) {
            return 'тисяча';
        } elseif ($lastDigit >= 2 && $lastDigit <= 4) {
            return 'тисячі';
        } else {
            return 'тисяч';
        }
    }
}
/*
// Приклад використання класу.
$numberToWords = new NumberToWords();
$number = 15147.58;
echo $numberToWords->convert($number);
*/
?>
