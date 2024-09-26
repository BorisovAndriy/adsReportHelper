<?php

namespace app\components;

use yii\base\Component;
use DateTime;
use Exception;

/**
 * Клас DateHandler призначений для роботи з датами у форматі ДД.ММ.РРРР ГГ:ХХ:СС
 */
class DateHandler extends Component
{
    /**
     * Метод, що приймає рядок дати і часу та повертає тільки дату.
     *
     * @param string $dateTimeString Рядок дати і часу у форматі "дд.мм.рррр гг:хх:сс"
     * @return string Дата у форматі "дд.мм.рррр"
     * @throws Exception У випадку некоректного формату дати
     */
    public function getDateFromDateTime(string $dateTimeString): string
    {
        // Перетворюємо рядок у об'єкт DateTime
        $dateTime = new DateTime($dateTimeString);

        // Повертаємо лише дату у форматі "дд.мм.рррр"
        return $dateTime->format('d.m.Y');
    }

    /**
     * Метод для перетворення дати з Excel у формат PHP.
     *
     * @param float $excelDate Дата у форматі Excel
     * @return string Дата у форматі "Y-m-d H:i:s"
     */
    public function excelDateToPHP(float $excelDate): string
    {
        // Дата відліку в Excel - це 1 січня 1900 року
        $unixDate = ($excelDate - 25569) * 86400; // 25569 - це кількість днів між 1 січня 1900 і 1 січня 1970 (стандарт Unix Epoch)
        return gmdate("Y-m-d H:i:s", $unixDate);
    }

    /**
     * Метод для генерування випадкової дати та часу між двома заданими датами.
     *
     * @param string $startDateTime Початкова дата і час у форматі "Y-m-d H:i:s"
     * @param string $endDateTime Кінцева дата і час у форматі "Y-m-d H:i:s"
     * @return string Випадкова дата і час між початковою та кінцевою у форматі "Y-m-d H:i:s"
     * @throws Exception У випадку некоректного формату дати
     */
    public function generateRandomDateTimeBetween(string $startDateTime, string $endDateTime): string
    {
        // Перетворюємо вхідні дати на об'єкти DateTime
        $start = new DateTime($startDateTime);
        $end = new DateTime($endDateTime);

        // Перевіряємо, що кінцева дата більша або дорівнює початковій
        if ($end <= $start) {
            throw new Exception('Кінцева дата повинна бути більшою за початкову дату.');
        }

        // Генеруємо випадковий timestamp між початковим і кінцевим
        $randomTimestamp = rand($start->getTimestamp(), $end->getTimestamp());

        // Перетворюємо timestamp назад у формат дати
        return gmdate("Y-m-d H:i:s", $randomTimestamp);
    }

    /**
     * Метод для генерування випадкової дати та часу в межах трьох місяців до заданої дати.
     *
     * @param string $dateTimeString Дата і час у форматі "Y-m-d H:i:s"
     * @return string Випадкова дата і час у форматі "Y-m-d H:i:s"
     * @throws Exception У випадку некоректного формату дати
     */
    public function generateRandomDateTimeFromPastThreeMonths(string $dateTimeString): string
    {
        // Перетворюємо вхідну дату на об'єкт DateTime
        $currentDate = new DateTime($dateTimeString);

        // Клонуюмо об'єкт поточної дати, щоб створити діапазон в три місяці
        $threeMonthsAgo = (clone $currentDate)->modify('-3 months');

        // Генеруємо випадкову дату між трьома місяцями тому і поточною датою
        return $this->generateRandomDateTimeBetween($threeMonthsAgo->format('Y-m-d H:i:s'), $currentDate->format('Y-m-d H:i:s'));
    }
}
