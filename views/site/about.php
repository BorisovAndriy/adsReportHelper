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
            'placeholder' => Yii::t('custom', 'Enter start date'),
            'class' => 'form-control input-3d']) ?>
    </div>

    <div class="form-group">
        <?= $form->field($model, 'periodTo')->textInput([
            'value' => $model->periodTo,
            'placeholder' => Yii::t('custom', 'Enter end date'),
            'class' => 'form-control input-3d']) ?>
    </div>

    <div class="form-group">
        <?= $form->field($model, 'docsNumber')->textInput([
            'value' => $model->docsNumber,
            'placeholder' => Yii::t('custom', 'Enter number of documents'),
            'class' => 'form-control input-3d']) ?>
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
        top: 300px; /* Початкова позиція над кошиком */
        left: 10%; /* Поряд з кошиком */
        animation: bounce-ball 0.5s infinite;
    }

    #dog {
        font-size: 50px;
        top: 350px; /* Початкова позиція над кошиком */
        left: 15%; /* Поряд з кошиком */
        transition: all 0.3s linear;
        animation: bounce-dog 0.8s infinite;
    }

    #basket {
        font-size: 35px; /* Кошик вдвічі менший */
        bottom: 30%; /* Вище на 30% від нижнього краю */
        left: 10%; /* Поруч з початковою позицією м'яча */
    }

    #counter {
        position: absolute;
        bottom: 40%; /* Лічильник вище кошика */
        left: 12%; /* Трохи ліворуч від кошика */
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


<script>
    $(document).ready(function() {
        let isBallInBasket = true;
        let ballCount = 0;  // Лічильник м'ячів

        // М'яч вилітає з кошика через певний час
        function launchBall() {
            if (isBallInBasket) {
                let randomX = Math.random() * ($(window).width() - 100);
                let randomY = Math.random() * ($(window).height() - 200);

                $('#ball').animate({
                    left: randomX,
                    top: randomY
                }, 1000, function() {
                    moveDogToBall(randomX, randomY); // Собака біжить за м'ячем
                });

                isBallInBasket = false;
            }
        }

        // Собака біжить до м'яча
        function moveDogToBall(ballX, ballY) {
            let dog = $('#dog');
            let currentLeft = dog.position().left;

            // Зміна напрямку собаки
            if (ballX < currentLeft) {
                dog.css('transform', 'scaleX(-1)');
            } else {
                dog.css('transform', 'scaleX(1)');
            }

            // Анімація руху собаки до м'яча
            dog.animate({
                left: ballX,
                top: ballY
            }, 1000, function() {
                carryBallToBasket();
            });
        }

        // Собака приносить м'яч до кошика
        function carryBallToBasket() {
            let dog = $('#dog');

            // М'яч слідує за собакою
            $('#ball').css({
                left: dog.position().left + 20 + 'px',
                top: dog.position().top + 20 + 'px',
                transition: 'none'
            });

            // Рухаємо собаку з м'ячем до кошика
            let basketLeft = $('#basket').position().left;
            let basketTop = $('#basket').position().top;

            dog.animate({
                left: basketLeft,
                top: basketTop - 50
            }, 1000, function() {
                placeBallInBasket();
            });

            // М'яч рухається разом з собакою
            $('#ball').animate({
                left: basketLeft + 20,
                top: basketTop - 50
            }, 1000);
        }

        // Поміщаємо м'яч в кошик
        function placeBallInBasket() {
            $('#ball').css({
                left: $('#basket').position().left + 20 + 'px',
                top: $('#basket').position().top - 50 + 'px'
            });

            // Оновлюємо лічильник
            ballCount++;
            $('#counter').text(ballCount);

            // Запускаємо кісточку після доставки м'яча
            setTimeout(launchBone, 1000);
        }

        // Запускаємо кісточку з кошика
        function launchBone() {
            $('<div id="bone">🦴</div>').appendTo('body');  // Додаємо кісточку до сторінки

            $('#bone').css({
                position: 'absolute',
                fontSize: '30px',
                left: $('#basket').position().left + 20 + 'px',
                top: $('#basket').position().top - 50 + 'px'
            });

            // Кісточка вилітає в довільне місце
            let randomX = Math.random() * ($(window).width() - 100);
            let randomY = Math.random() * ($(window).height() - 200);

            $('#bone').animate({
                left: randomX,
                top: randomY
            }, 1000, function() {
                moveDogToBone(randomX, randomY); // Собака біжить до кісточки
            });
        }

        // Собака біжить до кісточки
        function moveDogToBone(boneX, boneY) {
            let dog = $('#dog');
            let currentLeft = dog.position().left;

            // Зміна напрямку собаки
            if (boneX < currentLeft) {
                dog.css('transform', 'scaleX(-1)');
            } else {
                dog.css('transform', 'scaleX(1)');
            }

            // Анімація руху собаки до кісточки
            dog.animate({
                left: boneX,
                top: boneY
            }, 1000, function() {
                eatBone();
            });
        }

        // Собака їсть кісточку
        function eatBone() {
            $('#bone').remove();

            // Собака повертається до кошика після того, як з'їла кісточку
            let basketLeft = $('#basket').position().left;
            let basketTop = $('#basket').position().top;

            $('#dog').animate({
                left: basketLeft,
                top: basketTop - 50
            }, 1000, function() {
                launchBall(); // Запускаємо новий м'яч після того, як собака повернеться
            });
        }

        // Запускаємо м'яч після завантаження сторінки
        setTimeout(launchBall, 1000);
    });

</script>
