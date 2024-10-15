<?php

/** @var yii\web\View $this */

use yii\helpers\Html;

use yii\widgets\ActiveForm;
use kartik\datetime\DateTimePicker;
use lajax\languagepicker\widgets\LanguagePicker;

$this->title = 'About';
$this->params['breadcrumbs'][] = $this->title;
/*$form->field($model, 'periodFrom')->widget(DateTimePicker::class, [
            'options' => ['placeholder' => 'Виберіть дату та час початку'],
            'value' => $model->periodFrom,
            'pluginOptions' => [
                'autoclose' => true,
                'format' => 'dd.mm.yyyy hh:ii',  // Формат дати і часу
                'todayHighlight' => true,
            ]
        ]) */
?>

<div class="site-about">

    <?php $form = ActiveForm::begin([
        'options' => ['enctype' => 'multipart/form-data'], // Додаємо enctype для завантаження файлів
    ]); ?>

    <div class="form-group">
        <?= $form->field($model, 'periodFrom')->textInput([
            'value' => $model->periodFrom,
            'placeholder' => Yii::t('custom', 'Enter start date in format DD.MM.YYYY HH:MM:SS'),
            'class' => 'form-control input-3d'
        ]) ?>
    </div>

    <div class="form-group">
        <?= $form->field($model, 'periodTo')->textInput([
            'value' => $model->periodTo,
            'placeholder' => Yii::t('custom', 'Enter end date in format DD.MM.YYYY HH:MM:SS'),
            'class' => 'form-control input-3d'
        ]) ?>
    </div>

    <div class="form-group">
        <?= $form->field($model, 'docsNumber')->textInput([
            'value' => $model->docsNumber,
            'placeholder' => Yii::t('custom', 'Enter number of documents'),
            'class' => 'form-control input-3d'
        ]) ?>
    </div>

    <div class="form-group file-input-container">
        <?= $form->field($model, 'file')->fileInput(['class' => 'file-input input-3d']) ?>
    </div>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('custom', 'Submit'), ['class' => 'btn btn-primary btn-3d']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
<div id="ball">⚽</div>
<div id="dog">🐕</div>
<div id="basket">🧺</div>
<div id="counter">0</div>

<style>
    #ball, #dog, #basket {
        position: absolute;
    }

    #ball {
        font-size: 25px;
        bottom: 5%;
        right: 5%;
        animation: bounce-ball 0.5s infinite;
    }

    #dog {
        font-size: 50px;
        bottom: 5%;
        right: 10%;
        transition: transform 0.3s linear, all 0.3s linear;
        animation: bounce-dog 0.8s infinite;
    }

    #basket {
        font-size: 35px;
        bottom: 5%;
        right: 5%;
    }

    #counter {
        position: absolute;
        bottom: 20%;
        right: 7%;
        font-size: 40px;
        color: #FF4500;
        font-weight: bold;
        font-family: 'Courier New', Courier, monospace;
        text-shadow: 0 0 10px rgba(255, 69, 0, 0.7),
        0 0 20px rgba(255, 69, 0, 0.5),
        0 0 30px rgba(255, 69, 0, 0.3),
        0 0 40px rgba(255, 69, 0, 0.2);
    }

    @keyframes bounce-ball {
        0%, 100% {
            transform: translateY(0);
        }
        50% {
            transform: translateY(-20px);
        }
    }

    @keyframes bounce-dog {
        0%, 100% {
            transform: translateY(0);
        }
        50% {
            transform: translateY(-10px);
        }
    }
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    $(document).ready(function() {
        let isBallInBasket = true;  // Слідкуємо, чи м'яч у кошику
        let ballCount = 0;          // Лічильник доставлених м'ячів
        let isBoneActive = false;   // Слідкуємо, чи є кісточка на екрані

        // Функція для запуску м'яча до місця кліку
        function launchBall(event) {
            if (isBallInBasket && !isBoneActive) { // Перевірка, щоб м'яч не запускався під час активної кісточки
                let mouseX = event.pageX - 25; // Корекція для центру м'яча
                let mouseY = event.pageY - 25;

                // М'яч вилітає з кошика
                let basketPos = $('#basket').position();
                $('#ball').css({
                    left: basketPos.left + 20 + 'px',
                    top: basketPos.top - 50 + 'px'
                });

                // Анімація польоту м'яча до кліка
                $('#ball').animate({
                    left: mouseX,
                    top: mouseY
                }, 1000, function() {
                    moveDogToBall(mouseX, mouseY); // Собака біжить за м'ячем
                });

                isBallInBasket = false; // М'яч більше не в кошику
            }
        }

        // Функція для руху собаки до м'яча
        function moveDogToBall(ballX, ballY) {
            let dog = $('#dog');
            let currentLeft = dog.position().left;

            // Поворот собаки в сторону м'яча
            if (ballX < currentLeft) {
                dog.css('transform', 'scaleX(-1)');  // Поворот наліво
            } else {
                dog.css('transform', 'scaleX(1)');   // Поворот направо
            }

            // Анімація руху собаки до м'яча
            dog.animate({
                left: ballX,
                top: ballY
            }, 1000, function() {
                carryBallToBasket(); // Після досягнення м'яча - несе його назад
            });
        }

        // Функція для повернення собаки з м'ячем до кошика
        function carryBallToBasket() {
            let dog = $('#dog');
            let basketPos = $('#basket').position();

            // М'яч слідує за собакою
            $('#ball').css({
                left: dog.position().left + 20 + 'px',
                top: dog.position().top + 20 + 'px',
                transition: 'none'
            });

            // Собака і м'яч повертаються до кошика
            dog.animate({
                left: basketPos.left,
                top: basketPos.top - 50
            }, 1000, function() {
                placeBallInBasket(); // Після прибуття кладемо м'яч у кошик
            });

            // М'яч рухається разом із собакою
            $('#ball').animate({
                left: basketPos.left + 20,
                top: basketPos.top - 50
            }, 1000);
        }

        // Функція для поміщення м'яча в кошик і запуску кісточки
        function placeBallInBasket() {
            $('#ball').css({
                left: $('#basket').position().left + 20 + 'px',
                top: $('#basket').position().top - 50 + 'px'
            });

            // Оновлюємо лічильник
            ballCount++;
            $('#counter').text(ballCount);

            // М'яч повернувся до кошика
            isBallInBasket = true;

            // Запускаємо кісточку через секунду після повернення м'яча
            setTimeout(launchBone, 1000);
        }

        // Функція для запуску кісточки
        function launchBone() {
            isBoneActive = true; // Вказуємо, що кісточка активна

            $('<div id="bone">🦴</div>').appendTo('body'); // Створюємо кісточку

            $('#bone').css({
                position: 'absolute',
                fontSize: '30px',
                left: $('#basket').position().left + 20 + 'px',
                top: $('#basket').position().top - 50 + 'px'
            });

            // Випадкове місце для кісточки
            let randomX = Math.random() * ($(window).width() - 100);
            let randomY = Math.random() * ($(window).height() - 200);

            // Анімація польоту кісточки
            $('#bone').animate({
                left: randomX,
                top: randomY
            }, 1000, function() {
                moveDogToBone(randomX, randomY); // Собака біжить за кісточкою
            });
        }

        // Функція для руху собаки до кісточки
        function moveDogToBone(boneX, boneY) {
            let dog = $('#dog');
            let currentLeft = dog.position().left;

            // Поворот собаки в сторону кісточки
            if (boneX < currentLeft) {
                dog.css('transform', 'scaleX(-1)');  // Поворот наліво
            } else {
                dog.css('transform', 'scaleX(1)');   // Поворот направо
            }

            // Анімація руху собаки до кісточки
            dog.animate({
                left: boneX,
                top: boneY
            }, 1000, function() {
                eatBone(); // Собака "їсть" кісточку
            });
        }

        // Функція для "з'їдання" кісточки
        function eatBone() {
            $('#bone').remove(); // Видаляємо кісточку

            let basketPos = $('#basket').position();

            // Поворот собаки в бік кошика
            $('#dog').css('transform', 'scaleX(1)');

            // Собака повертається до кошика
            $('#dog').animate({
                left: basketPos.left,
                top: basketPos.top - 50
            }, 1000, function() {
                isBoneActive = false; // Кісточка зникла, можна знову запускати м'яч
            });
        }

        // Обробляємо клік на документі, щоб м'яч вилітав до курсора миші
        $(document).on('click', function(e) {
            launchBall(e); // Запускаємо м'яч на місце кліку миші
        });
    });
</script>
