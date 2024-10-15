<?php

/** @var yii\web\View $this */
use yii\helpers\Url;

$this->title = Yii::t('custom', 'My Application');

?>
<div class="site-index">

    <div class="jumbotron text-center bg-transparent">
        <h1 class="display-4"><?= Yii::t('custom', 'Congratulations') ?>!</h1>
        <p class="lead">
            <div class="alert-custom">
                <p class="lead">Вітаємо вас, шановний користувач! 🎉</p>
                <p>Бажаємо вам мирного неба, продуктивної роботи та гарного настрою.<br>
                    Нехай ваш день буде сповнений нових досягнень, а звіти складатимуться легко й без помилок. <br>
                    Глибокого натхнення та успіхів у всіх ваших справах! ☀️</p>
                <hr>
                <p class="mb-0">З найкращими побажаннями, <strong>Звітобот</strong>.</p>
            </div>



        </p>

        <p><a class="btn btn-lg btn-success" href="<?= Url::to(['/site/about']) ?>">Позпочати роботу з документами</a></p>
    </div>

    <div class="body-content">
        <!-- Перша частина -->
        <div class="row">
            <div class="col-lg-4">
                <h2>Підготовка документації</h2>
                <p>Наша система надає можливість автоматичного генерування документів, таких як звіти, акти та рахунки, із точним підрахунком усіх необхідних даних. Всі документи створюються згідно заздалегідь підготовлених шаблонів та можуть бути завантажені у форматі Excel. Це дозволяє значно оптимізувати процеси, скорочує ризик помилок і економить час на підготовку документації. Система забезпечує повну автоматизацію формування фінансових та звітних документів, включаючи обчислення сум, дат, періодів та інших даних в підзвітних документах.</p>
            </div>

            <div class="col-lg-4">
                <h2>Лише один файл</h2>
                <p>Наш сервіс пропонує просте та зручне рішення: завантажте лише один файл, і в результаті ви отримаєте архів, який містить усі підготовлені звітні документи. Це значно спрощує процес, адже всі акти, рахунки та інші необхідні файли автоматично генеруються згідно з вашими даними, підраховуються всі необхідні показники, і все це зібрано в одному архіві. Ви отримуєте готовий комплект документів, які можна одразу використовувати для подачі звітності або передачі в банк.</p>
            </div>

            <div class="col-lg-4">
                <h2>Для ФОПів 3-ї групи</h2>
                <p>Наш сервіс підходить для ФОПів 3-ї групи, які регулярно подають звітність та здійснюють виплати через систему Admitad. Завдяки автоматичному генеруванню звітних документів, таких як акти, звіти та рахунки, сервіс значно спрощує процес підготовки звітності. Він автоматично підраховує всі необхідні дані, формує документи відповідно до вимог та дозволяє швидко завантажувати їх у форматі Excel для подальшого подання в банк. Це економить час, мінімізує ризики помилок і забезпечує точність в усіх розрахунках, що робить його незамінним інструментом для ФОПів, які працюють з Admitad.</p>
            </div>

            <div class="col-lg-4">
                <h2>Заповнення періоду</h2>
                <p>На цьому етапі ви вказуєте дату початку та завершення періоду, протягом якого відбувалися події, що стосуються звіту. Дати повинні бути введені у форматі дд.мм.рррр. Переконайтеся, що ці дати є коректними.</p>
            </div>

            <!-- Друга частина -->
            <div class="col-lg-4">
                <h2>Номера документів</h2>
                <p>Введіть порядковий номер документів у звітності за вказаний період. Це може бути номер договору, акта або іншого документа. Поле дозволяє вводити тільки цілі числа, тому переконайтеся у правильності введених даних.</p>
            </div>

            <!-- Третя частина -->
            <div class="col-lg-4">
                <h2>Завантаження файлу</h2>
                <p>Останній етап - це завантаження файлу. Ви можете завантажити файл у форматі .xlsx або .xls, який містить додаткові дані або документи, пов'язані з періодом. Після вибору файлу переконайтеся, що він успішно доданий.</p>
            </div>
        </div>
    </div>


</div>

<div id="zvitobot">🤖</div>
<div id="basket">🧺 Кошик</div>
<div id="report">📄</div>
<p id="counter">Звіти доставлені: 0</p>

<style>
    body {
        margin: 0;
        overflow: hidden;
        height: 100vh;
        width: 100vw;
    }

    #zvitobot {
        position: absolute;
        font-size: 40px;
        transition: top 1s ease, left 1s ease; /* Плавний рух */
    }

    #basket {
        position: fixed;
        bottom: 10px;
        left: 10px;
        font-size: 50px;
    }

    #report {
        position: absolute;
        font-size: 40px;
        transition: top 1s ease, left 1s ease; /* Плавний рух */
    }

    #counter {
        position: fixed;
        top: 10px;
        left: 10px;
        font-size: 20px;
    }
</style>

<script>
    const bot = document.getElementById("zvitobot");
    const basket = document.getElementById("basket");
    const report = document.getElementById("report");
    const counterDisplay = document.getElementById("counter");
    let reportCounter = 0;

    // Розміщуємо звітоБота у кошика на початку
    function setBotAtBasket() {
        bot.style.left = basket.offsetLeft + "px";
        bot.style.top = basket.offsetTop + "px";
    }

    // Функція для оновлення лічильника
    function updateCounter() {
        reportCounter++;
        counterDisplay.innerText = `Звіти доставлені: ${reportCounter}`;
    }

    // Функція для переміщення звіту на випадкове місце в межах екрану
    function moveReportToRandomPosition() {
        const windowWidth = window.innerWidth;
        const windowHeight = window.innerHeight;

        // Генеруємо рандомні координати для звіту в межах екрану
        const newLeft = Math.floor(Math.random() * (windowWidth - report.offsetWidth));
        const newTop = Math.floor(Math.random() * (windowHeight - report.offsetHeight));

        // Рухаємо звіт на нове місце
        report.style.left = newLeft + "px";
        report.style.top = newTop + "px";
    }

    // Функція для руху звітоБота до елемента (звіт або кошик)
    function moveZvitobotTo(target) {
        const targetLeft = target.offsetLeft;
        const targetTop = target.offsetTop;

        bot.style.left = targetLeft + "px";
        bot.style.top = targetTop + "px";
    }

    // Функція для початку циклу руху звітоБота
    function startBotMovement() {
        // ЗвітоБот рухається до звіту
        moveZvitobotTo(report);

        setTimeout(function() {
            // Після досягнення звіту, звіт рухається разом із звітоБотом
            report.style.left = bot.style.left;
            report.style.top = bot.style.top;

            setTimeout(function() {
                // ЗвітоБот несе звіт до кошика
                moveZvitobotTo(basket);

                setTimeout(function() {
                    // Після доставки до кошика звіт зникає
                    report.style.left = "-100px";
                    report.style.top = "-100px";

                    // Оновлюємо лічильник
                    updateCounter();

                    // Через невеликий час з'являється новий звіт
                    setTimeout(function() {
                        moveReportToRandomPosition();
                    }, 1000);

                }, 2000); // Час, необхідний для того, щоб звітоБот доніс звіт до кошика

            }, 1000); // Час, необхідний для захоплення звіту
        }, 2000); // Час, необхідний для досягнення звіту
    }

    // Кожні 6 секунд починаємо новий цикл руху
    setInterval(startBotMovement, 6000);

    // Перший запуск
    moveReportToRandomPosition(); // Спочатку показуємо звіт на випадковій позиції
    setBotAtBasket(); // Розміщуємо звітоБота біля кошика
    startBotMovement(); // І запускаємо рух звітоБота
</script>