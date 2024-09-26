<?php

namespace app\components;

use yii\base\Component;
use GuzzleHttp\Client;

class AI extends Component
{
    public $model; // Назва моделі, яку використовуємо (gpt-3.5-turbo)
    public $apiKey; // Ваш API-ключ для доступу до OpenAI API
    public $maxTokens = 100; // Максимальна кількість токенів, яку повертає модель

    // Конструктор класу, який задає значення моделі за замовчуванням
    public function __construct($model = 'gpt-3.5-turbo', $config = [])
    {
        $this->model = $model;
        parent::__construct($config);
    }

    // Функція, яка виконує запит до OpenAI API для отримання відповіді
    public function generateResponse($input)
    {
        // Перевіряємо, чи введено запит
        if (empty($input)) {
            return "Будь ласка, введіть запит.";
        }

        // Створюємо запит до API OpenAI
        try {
            $client = new Client(); // Створюємо клієнта для відправки HTTP-запитів
            $response = $client->post('https://api.openai.com/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey, // Передаємо API-ключ
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => $this->model, // Вказуємо модель (gpt-3.5-turbo)
                    'messages' => [
                        [
                            'role' => 'system', // Початкове повідомлення системи
                            'content' => 'You are a helpful assistant.', // Установлюємо роль асистента
                        ],
                        [
                            'role' => 'user', // Повідомлення від користувача
                            'content' => $input, // Запит, введений користувачем
                        ],
                    ],
                    'max_tokens' => $this->maxTokens, // Максимальна кількість токенів
                ],
            ]);

            // Отримуємо та обробляємо відповідь від API
            $data = json_decode($response->getBody()->getContents(), true);
            return $data['choices'][0]['message']['content'] ?? "Немає відповіді."; // Повертаємо текст відповіді або повідомлення про відсутність
        } catch (\Exception $e) {
            return "Помилка: " . $e->getMessage(); // Повертаємо повідомлення про помилку, якщо щось пішло не так
        }
    }
}
