<?php

namespace app\components;

use Yii;
use yii\base\BootstrapInterface;

class LanguageSelector implements BootstrapInterface
{
    public function bootstrap($app)
    {
        // Запускаємо сесію, якщо вона ще не запущена
        if (!Yii::$app->session->isActive) {
            Yii::$app->session->open();
        }

        // Перевіряємо мову в запиті або в cookie
        $language = $app->request->get('language') ?? $app->request->cookies->getValue('language', 'uk');

        // Якщо є валідна мова, зберігаємо її в сесії і cookie
        if (in_array($language, ['en', 'uk'])) {
            $app->session->set('language', $language);
            $app->response->cookies->add(new \yii\web\Cookie([
                'name' => 'language',
                'value' => $language,
                'expire' => time() + (60 * 60 * 24 * 30), // Мова зберігається на 30 днів
            ]));
        } else {
            // Якщо немає параметру, беремо мову із сесії або cookie
            $language = $app->session->get('language', 'uk');
        }

        // Встановлюємо мову додатку
        $app->language = $language;
    }
}
