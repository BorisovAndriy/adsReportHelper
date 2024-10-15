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


    public function actionAbout()
    {


        $totalSumma = 0;
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
                    }
                    $datas[] = $rowData; // Додати рядок до масиву
                }

                foreach ($datas as $data){
                    $tmpFloat = floatval($data[4]);
                    if (!empty($tmpFloat)){
                        $totalSumma += $tmpFloat;
                    }
                }

            } catch (Exception $e) {
                echo 'Caught exception: ',  $e->getMessage(), "\n";
            }


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

            $zipArchive = $description = false;

            $fileHandler->createZipArchive($files, Yii::getAlias('@webroot') . '/source/'.$docsNumber.'_archive.zip');

            $zipArchive = Yii::getAlias('@webroot') . '/source/'.$docsNumber.'_archive.zip';

            return Yii::$app->response->sendFile($zipArchive);

        }

        return $this->render('about', ['model' => $model]);
    }

}
