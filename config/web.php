<?php
$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'language' => 'uk', // Встановлюємо українську за замовчуванням
    'sourceLanguage' => 'uk', // Мова оригіналу
    'bootstrap' => ['log', 'languageSelector'],

    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],

    'components' => [
        'session' => [
            'class' => 'yii\web\Session',
            'timeout' => 1440,  // Час зберігання сесії (в секундах)
            'cookieParams' => ['httponly' => true, 'secure' => false], // Параметри cookies сесії
        ],

        // Компонент для зміни мови
        'languageSelector' => [
            'class' => 'lajax\languagepicker\Component',
            // Доступні мови для вибору
            'languages' => ['uk', 'en'],
            'cookieName' => 'language', // Зберігаємо мову в cookies
            'expireDays' => 30, // Час зберігання cookies (у днях)
        ],
        // Налаштування мультимовності
        'i18n' => [
            'translations' => [
                '*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@app/messages',
                    'sourceLanguage' => 'en',
                    'fileMap' => [
                        'custom' => 'custom.php',
                    ],
                ],
            ],
        ],
        // Налаштування URL Manager для мультимовності
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'enableStrictParsing' => false,
            'rules' => [
                '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
                '/' => 'site/index',
            ],
        ],

        // Компонент AI з правильною конфігурацією
        'ai' => [
            'class' => 'app\components\AI', // Вказуємо шлях до компонента AI
            'model' => 'gpt-3.5-turbo', // Використовуємо модель gpt-3.5-turbo
            'maxTokens' => 150, // Максимальна кількість токенів у відповіді
            'apiKey' => '',  // Реальний API-ключ
        ],

        // Інші компоненти
        'request' => [
            'class' => 'yii\web\Request',
            'cookieValidationKey' => 'MyVo5rrxdAj7R7KxLyxNSJcODhIGe-1m',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}

return $config;
