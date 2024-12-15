<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Форма связи</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="wrapper">
    <header class="header">
        <div class="container">
            <div class="header__content">
                Шапка сайта
            </div>
        </div>
    </header>
<link rel="stylesheet" href="style.css">
    <main class="content">
        <div class="container">


            <div class="form-block">
                <h1>Форма связи</h1>
                <form id="form" enctype="multipart/form-data">
                    <input class="clean" type="text" name="name" placeholder="Имя">
                    <input class="clean" type="email" name="email" placeholder="Email *">
                    <textarea class="clean" rows="3" name="text" placeholder="Текст сообщения"></textarea>
                    <input class="clean" type="file" name="file[]" multiple="multiple">
                    <button name="send" type="submit">Отправить</button>
                </form>
            </div>


        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer__content">
                Подвал сайта
            </div>
        </div>
    </footer>
</div>

<script src="scripts.js"></script>

</body>
</html>