<?php

return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=127.0.0.1:3306;dbname=mompe',
    'username' => 'root',  // або інший користувач
    'password' => 'root',  // пароль користувача
    'charset' => 'utf8',

    // Увімкнення кешування для покращення продуктивності
    'enableSchemaCache' => true,

    // Тривалість збереження кешу схеми в секундах
    'schemaCacheDuration' => 3600,

    // Назва компонента кешування
    'schemaCache' => 'cache',
];
