<?php

namespace app\controllers;

use Yii;
use yii\base\BaseObject;
use yii\filters\AccessControl;
use yii\helpers\VarDumper;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\PeriodForm;
use yii2tech\spreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use yii\data\ArrayDataProvider;
//use PhpOffice\PhpSpreadsheet\Spreadsheet;
//use PhpOffice\PhpSpreadsheet\IOFactory;

use \app\components\DateHandler;
use \app\components\FileHandler;
use \app\components\ActGenerator;
use \app\components\InvoiceGenerator;
use \app\components\ReportGenerator;
use \app\components\DateTime;
use yii\web\UploadedFile;

ini_set('pcre.backtrack_limit', '2000000');
set_time_limit(300);

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Дія для генерації відповіді AI на запит.
     *
     * @return string Рендерить відповідь штучного інтелекту.
     */
    public function actionAiTest()
    {
        // Отримуємо запит від користувача
        $input = Yii::$app->request->get('query', 'Привіт, як справи?');

        // Використовуємо компонент AI для генерації відповіді
        $response = Yii::$app->ai->generateResponse($input);

        // Повертаємо згенеровану відповідь на сторінку
        return $this->render('ai-test', ['response' => $response]);
    }

// Приклад використання:
//echo numberToText(123); // виведе: "сто двадцять три"


//-12 961,50 UAH ID 942987 statement_detailed_942987
    public function actionAbout()
    {


        $totalSumma = 0;



/*
        $periodFrom = '03.09.2024  14:45:25';
        $periodFTo = '22.09.2024  14:45:25';
        $docsNumber = 329;
*/
        $model = new PeriodForm();


        if ($model->load(Yii::$app->request->post()) && $model->validate()) {


            // Завантаження файлу
            $model->file = UploadedFile::getInstance($model, 'file');

            if ($model->file) {
                // Генерація унікального імені для файлу
                $uniqueFileName = uniqid() . '_' . $model->file->baseName . '.' . $model->file->extension;

                // Збереження файлу в директорію uploads
                $uploadPath = Yii::getAlias('@webroot') . '/uploads/' . $uniqueFileName;
                if ($model->file->saveAs($uploadPath)) {
                    // Якщо файл успішно завантажено, використовуємо його
                    $inputFileName = $uploadPath; // Встановлюємо шлях до завантаженого файлу
                } else {
                    Yii::$app->session->setFlash('error', 'Не вдалося завантажити файл.');
                    return $this->render('about', ['model' => $model]);
                }
            } else {
                Yii::$app->session->setFlash('error', 'Файл не вибрано.');
                return $this->render('about', ['model' => $model]);
            }

            // Успішна обробка даних, наприклад:
            $periodFrom = $model->periodFrom;
            $periodTo = $model->periodTo;
            $docsNumber = $model->docsNumber;



            try {
                // Шлях до Excel файлу


                $inputFileName = $inputFileName;
                $totalSumma = 0;
                //$inputFileName = 'source/act_template.xlsx';
                $spreadsheet = IOFactory::load($inputFileName);
                $sheet = $spreadsheet->getActiveSheet();

                $dateHandler = new DateHandler;

                foreach ($sheet->getRowIterator() as $row) {
                    $cellIterator = $row->getCellIterator();
                    $cellIterator->setIterateOnlyExistingCells(false); // Включити порожні клітинки

                    $rowData = [];
                    foreach ($cellIterator as $cell) {
                        $rowData[] = $cell->getValue(); // Отримати значення клітинки


                        /*

                        $rowData[] = $dateHandler->excelDateToPHP($cell->getValue());

                        if (is_float($value)) {
                            $value = $dateHandler->excelDateToPHP($cell->getValue());
                        }
                        */
                    }
                    $datas[] = $rowData; // Додати рядок до масиву
                }

                /*
                foreach ($datas as &$data){

                    $data[3] = '01.09.2024  14:45:25';
                    $data[4] = '22.09.2024  14:45:25';
                }

                // Повернення даних у вигляді JSON (можна також відобразити у вигляді таблиці)
/*
                echo '<pre>';
                VarDumper::dump($datas[2]);
                die();
*/
                foreach ($datas as $data){
                    //var_dump($data);
                    //die();
                    $tmpFloat = floatval($data[4]);
                    if (!empty($tmpFloat)){
                        $totalSumma += $tmpFloat;
                    }
                }

            } catch (Exception $e) {
                echo 'Caught exception: ',  $e->getMessage(), "\n";
            }

            /*
            echo '<pre>';
            var_dump($datas[3]);
            die();
*/


/*
            var_dump($totalSumma);;
            die();
*/

            // Далі робимо щось з даними, наприклад, зберігаємо в БД або обробляємо
            $actGenerator = new ActGenerator();
            $invoiceGenerator = new InvoiceGenerator();
            $reportGenerator = new ReportGenerator();
            $fileHandler = new FileHandler();

            $outputActFileName = $actGenerator->generateAct($totalSumma, $model->periodFrom, $model->periodTo, $model->docsNumber);
            $files[] = $outputActFileName;


            $outputInvoiceFileName = $invoiceGenerator->generateInvoice($totalSumma, $model->periodFrom, $model->periodTo, $model->docsNumber);
            $files[] = $outputInvoiceFileName;


            $outputReportFileName = $reportGenerator->generateReport($totalSumma, $model->periodFrom, $model->periodTo, $model->docsNumber, $datas);
            $files[] = $outputReportFileName;

            $fileHandler->createZipArchive($files, Yii::getAlias('@webroot') . '/source/'.$docsNumber.'_archive.zip');
            die('complete');
            //$invoiceGenerator->generateInvoice($totalSumma, $model->periodFrom, $model->periodTo, $model->docsNumber);

           // $outputActFileName = 'source/' . $docsNumber . '_act_andriy_borisov.xlsx';
            //$outputActFileName = 'source/' . $docsNumber . '_invoice_andriy_borisov.xlsx';






            //$path = __DIR__ . DIRECTORY_SEPARATOR . 'Согласие.pdf';
            /*
            $file_get_contents = file_get_contents($outputActFileName);
            header("Content-type:application/xlsx");
            header("Content-Disposition:attachment;filename= " . $docsNumber . "_act_andriy_borisov.xlsx");
            header("Content-Length:" . filesize($outputActFileName));
            echo $file_get_contents;



            $outputActFileName = 'source/' . $docsNumber . '_invoice_andriy_borisov.xlsx';
            $invoiceGenerator->generateInvoice($totalSumma, $model->periodFrom, $model->periodTo, $model->docsNumber);

            $file_get_contents = file_get_contents($outputActFileName);
            header("Content-type:application/xlsx");
            header("Content-Disposition:attachment;filename= " . $docsNumber . "_invoice_andriy_borisov.xlsx");
            header("Content-Length:" . filesize($outputActFileName));
            echo $file_get_contents;
                */

            return $this->render('about', ['model' => $model]);
        }






        return $this->render('about', ['model' => $model]);
    }
        /*
            $exporter = new Spreadsheet([
                'dataProvider' => new ArrayDataProvider([
                    'allModels' => [
                        [
                            'name' => 'some name',
                            'price' => '9879',
                        ],
                        [
                            'name' => 'name 2',
                            'price' => '79',
                        ],
                    ],
                ]),
                'columns' => [
                    [
                        'attribute' => 'name',
                        'contentOptions' => [
                            'alignment' => [
                                'horizontal' => 'center',
                                'vertical' => 'center',
                            ],
                        ],
                    ],
                    [
                        'attribute' => 'price',
                    ],
                ],
            ]);
            $exporter->save('source/statement_detailed_yii2.xlsx');
            die();
           // return $this->asJson($data);

        } catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }


        die('actionAbout');
        return $this->render('about');
        */

}
