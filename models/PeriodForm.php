<?php
namespace app\models;

use yii\base\Model;
use yii\web\UploadedFile;

class PeriodForm extends Model
{
    public $periodFrom;
    public $periodTo;
    public $docsNumber;
    public $file;

    public function rules()
    {
        return [
            [['periodFrom', 'periodTo', 'docsNumber'], 'required'],

            // Валідація формату дат "день.місяць.рік години:хвилини:секунди"
            ['periodFrom', 'validateDate'],
            ['periodTo', 'validateDate'],

            // Перевірка, що periodTo більше або дорівнює periodFrom
            ['periodTo', 'compareDates'],

            // Валідація кількості документів як цілого числа
            ['docsNumber', 'integer', 'min' => 1],

            // Валідація завантаженого файлу
            ['file', 'file', 'skipOnEmpty' => false, 'extensions' => 'xlsx, xls'],
        ];
    }

    // Метод для валідації дат
    public function validateDate($attribute)
    {
        $date = \DateTime::createFromFormat('d.m.Y H:i:s', $this->$attribute);

        if (!$date || $date->format('d.m.Y H:i:s') !== $this->$attribute) {
            $this->addError($attribute, \Yii::t('custom', 'Невірний формат дати. Використовуйте DD.MM.YYYY HH:MM:SS'));
        }
    }

    // Метод для порівняння periodFrom і periodTo
    public function compareDates($attribute)
    {
        $from = \DateTime::createFromFormat('d.m.Y H:i:s', $this->periodFrom);
        $to = \DateTime::createFromFormat('d.m.Y H:i:s', $this->periodTo);

        if ($from && $to && $from > $to) {
            $this->addError($attribute, \Yii::t('custom', 'Дата закінчення повинна бути пізніше або дорівнювати даті початку'));
        }
    }

    // Метод для перекладу назв полів
    public function attributeLabels()
    {
        return [
            'periodFrom' => \Yii::t('custom', 'Дата початку'),
            'periodTo' => \Yii::t('custom', 'Дата закінчення'),
            'docsNumber' => \Yii::t('custom', 'Кількість документів'),
            'file' => \Yii::t('custom', 'Завантажити файл'),
        ];
    }
}

