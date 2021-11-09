<?php

function getLang()
{
    return 'ru';
}


$lang = [
    'ru' => [
        'title' => 'Распаковка репозитория',
        'form' => [
            'fields' => [
                'linkToRepo' => 'Ссылка на репо',
                'login' => 'Логин от git',
                'password' => 'Пароль',
            ],
            'buttons' => [
                'start' => 'Распаковать репо',
            ]
        ]
    ]
];


$message = $lang[getLang()];
?>

<html>
<head>
    <title><?= $message['title'] ?></title>
</head>
<body>


<div class="repoUnPacker">

    <form class="repoUnPacker__form">

        <div class="repoUnPacker__form-title">
            <?= $message['title'] ?>
        </div>

        <div class="repoUnPacker__form-item">
            <div class="repoUnPacker__form-item-title">
                <?= $message['form']['fields']['linkToRepo'] ?> <span style="color: red;">*</span>
            </div>
            <label>
                <input required class="repoUnPacker__form-input" type="text" name="repo">
            </label>
        </div>

        <div class="repoUnPacker__form-item">
            <div class="repoUnPacker__form-item-title">
                <?= $message['form']['fields']['login'] ?>
            </div>
            <label>
                <input class="repoUnPacker__form-input" type="text" name="repo">
            </label>
        </div>

        <div class="repoUnPacker__form-item">
            <div class="repoUnPacker__form-item-title">
                <?= $message['form']['fields']['password'] ?>
            </div>
            <label>
                <input class="repoUnPacker__form-input" type="text" name="repo">
            </label>
        </div>

        <div class="repoUnPacker__form-item">
            <button class="repoUnPacker__form-button" type="submit"><?= $message['form']['buttons']['start'] ?></button>
        </div>

    </form>

</div>

<style>
    .repoUnPacker {
        width: 100%;
        max-width: 400px;
        margin: 0 auto;
        margin-top: 100px;
    }

    .repoUnPacker__form-item {
        margin-bottom: 8px;
    }

    .repoUnPacker__form-item-title {
        font-size: 16px;
        margin-bottom: 4px;
        font-weight: bold;
    }

    .repoUnPacker__form-button {
        width: 100%;
        padding: 10px;
        cursor: pointer;
    }

    .repoUnPacker__form-input {
        width: 100%;
        padding: 8px;
    }

    .repoUnPacker__form-title {
        font-size: 22px;
        margin-bottom: 20px;
    }
</style>
<script>

</script>
</body>
</html>