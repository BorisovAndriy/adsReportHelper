<?php
namespace app\models;

use yii\base\Model;
use yii\web\UploadedFile;

class PeriodForm extends Model
{
    public $periodFrom;
    public $periodTo;
    public $docsNumber;
    public $file; // Поле для завантаження файлу

    public function rules()
    {
        return [
            [['periodFrom', 'periodTo', 'docsNumber'], 'required'],

            // Валідація дати у форматі "день.місяць.рік години:хвилини:секунди"
            ['periodFrom', 'match', 'pattern' => '/^\d{2}\.\d{2}\.\d{4} \d{2}:\d{2}:\d{2}$/', 'message' => \Yii::t('custom', 'Invalid date format. Use DD.MM.YYYY HH:MM:SS')],
            ['periodTo', 'match', 'pattern' => '/^\d{2}\.\d{2}\.\d{4} \d{2}:\d{2}:\d{2}$/', 'message' => \Yii::t('custom', 'Invalid date format. Use DD.MM.YYYY HH:MM:SS')],

            // Перевірка, що період закінчення не раніше за період початку
            ['periodTo', 'compare', 'compareAttribute' => 'periodFrom', 'operator' => '>=', 'type' => 'datetime', 'message' => \Yii::t('custom', 'End date must be later than or equal to start date')],

            // Валідація кількості документів як цілого числа
            ['docsNumber', 'integer', 'min' => 1],

            // Валідація завантаженого файлу
            ['file', 'file', 'skipOnEmpty' => false, 'extensions' => 'xlsx, xls'], // Додано правило для файлу
        ];
    }

    // Додаємо метод для перекладу назв полів
    public function attributeLabels()
    {
        return [
            'periodFrom' => \Yii::t('custom', 'Start Date'),
            'periodTo' => \Yii::t('custom', 'End Date'),
            'docsNumber' => \Yii::t('custom', 'Number of Documents'),
            'file' => \Yii::t('custom', 'Upload File'),
        ];
    }
}
