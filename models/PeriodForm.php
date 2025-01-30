<?php
namespace app\models;

use yii\base\Model;
use yii\web\UploadedFile;

/**
 * Клас PeriodForm представляє форму для введення періоду часу,
 * кількості документів та завантаження файлу.
 */
class PeriodForm extends Model
{
    /** @var string Дата початку періоду */
    public $periodFrom;

    /** @var string Дата закінчення періоду */
    public $periodTo;

    /** @var int Номер документу */
    public $docsNumber;

    /** @var UploadedFile Завантажений файл */
    public $file;

    /**
     * Визначення правил валідації для форми
     *
     * @return array Масив правил валідації
     */
    public function rules()
    {
        return [
            [['periodFrom', 'periodTo', 'docsNumber'], 'required'],

            // Валідація формату дат "день.місяць.рік"
            ['periodFrom', 'validateDate'],
            ['periodTo', 'validateDate'],

            // Перевірка, що periodTo більше або дорівнює periodFrom
            ['periodTo', 'compareDates'],

            // Валідація номера документу як цілого числа
            ['docsNumber', 'integer', 'min' => 1],

            // Валідація завантаженого файлу
            ['file', 'file', 'skipOnEmpty' => false, 'extensions' => 'xlsx, xls'],
        ];
    }

    /**
     * Метод для валідації дат у форматі "день.місяць.рік"
     *
     * @param string $attribute Назва атрибута, що перевіряється
     */
    public function validateDate($attribute)
    {
        if (!\DateTime::createFromFormat('d.m.Y', $this->$attribute)) {
            $this->addError($attribute, \Yii::t('custom', 'Невірний формат дати. Використовуйте DD.MM.YYYY'));
        }
    }

    /**
     * Метод для порівняння periodFrom і periodTo
     *
     * @param string $attribute Назва атрибута, що перевіряється
     */
    public function compareDates($attribute)
    {
        $from = \DateTime::createFromFormat('d.m.Y', $this->periodFrom);
        $to = \DateTime::createFromFormat('d.m.Y', $this->periodTo);

        if ($from && $to && $from > $to) {
            $this->addError($attribute, \Yii::t('custom', 'Дата закінчення повинна бути пізніше або дорівнювати даті початку'));
        }
    }

    /**
     * Метод для перекладу назв полів
     *
     * @return array Масив назв полів
     */
    public function attributeLabels()
    {
        return [
            'periodFrom' => \Yii::t('custom', 'Дата початку'),
            'periodTo' => \Yii::t('custom', 'Дата закінчення'),
            'docsNumber' => \Yii::t('custom', 'Номер документу'),
            'file' => \Yii::t('custom', 'Завантажити файл'),
        ];
    }
}