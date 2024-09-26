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
            [['periodFrom', 'periodTo'], 'datetime', 'format' => 'php:d.m.Y H:i:s'],
            [['docsNumber'], 'integer'],
            [['file'], 'file', 'skipOnEmpty' => false, 'extensions' => 'xlsx, xls'], // Додано правило для файлу
        ];
    }

    // Додаємо метод для перекладу назв полів
    public function attributeLabels()
    {
        return [
            'periodFrom' => \Yii::t('custom', 'Period From'),
            'periodTo' => \Yii::t('custom', 'Period To'),
            'docsNumber' => \Yii::t('custom', 'Docs Number'),
            'file' => \Yii::t('custom', 'File'),
        ];
    }
}
