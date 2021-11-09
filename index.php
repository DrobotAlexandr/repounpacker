<?php

unpackRepo();

function unpackRepo()
{
    $action = $_POST['action'];
    $params['repo'] = $_POST['repo'];
    $params['login'] = $_POST['login'];
    $params['password'] = $_POST['password'];

    if ($action != 'unpack') {
        return false;
    }

    $time = time() + 3600 * 24 * 7;

    setcookie('_deployGITR', $params['repo'], $time, '/');
    setcookie('_deployGITL', $params['login'], $time, '/');
    setcookie('_deployGITP', $params['password'], $time, '/');

    $response['status'] = 'ok';

    $validate = unpackRepo__validate($params);

    if ($validate) {
        $validate['data'] = '<pre>' . $validate['errorData'] . '</pre>';
        response($validate);
    }

    $params['repo'] = unpackRepo_getRepo($params);

    ob_start();

    try {

        $checkAuth = cloneRepo($params, false);

        if (!strstr($checkAuth, 'Authentication')) {
            $data = cloneRepo($params);

            if (strstr($data[0], "fatal: destination path '.' already exists and is not an empty directory.")) {
                removeFilesAndDirs();
                sleep(1);
                $data = cloneRepo($params);
            }

        }
        echo '<pre>';
        print_r($data);
        echo '</pre>';
    } catch (Throwable  $t) {
        echo "<pre>$t</pre>";
    }

    $res = ob_get_contents();
    $res = strtr($res, ['Array' => '', $params['password'] => '***']);
    ob_end_clean();

    $response['data'] = $res;

    response($response);

}

function removeFilesAndDirs()
{
    $data = scandir($_SERVER['DOCUMENT_ROOT']);


    foreach ($data as $item) {

        if ($item == '.' OR $item == '..') {
            continue;
        }

        @unlink($_SERVER['DOCUMENT_ROOT'] . '/' . $item);
        @rmdir($_SERVER['DOCUMENT_ROOT'] . '/' . $item);
    }

}

function unpackRepo_getRepo($params)
{
    $type = false;
    $name = false;

    if (strstr($params['repo'], 'github')) {
        $type = 'github';
        $name = strtr($params['repo'], ['https://github.com/' => '']);
    }

    if (strstr($params['repo'], 'bitbucket')) {
        $type = 'bitbucket';


        $name = strtr($params['repo'], ['git clone' => '']);
        $name = strtr($name, ['/src/master/' => '']);

        if (strstr($name, ':') AND strstr($name, '@')) {
            $name = explode(':', $name)[1];
        }

        if (strstr($name, 'https://bitbucket.org/')) {
            $name = explode('https://bitbucket.org/', $name)[1];
        }

        $name = trim($name);

    }

    return [
        'type' => $type,
        'name' => $name
    ];
}

function cloneRepo($params, $dot = '.')
{

    $rootDir = $_SERVER['DOCUMENT_ROOT'];

    $ex = '';

    if (!$params['login']) {
        $params['login'] = 'none';
    }

    if (!$params['password']) {
        $params['login'] = 'password';
    }

    if ($params['repo']['type'] == 'bitbucket') {
        $ex = 'git clone https://' . $params['login'] . ':' . $params['password'] . '@bitbucket.org/' . $params['repo']['name'];
    }

    if ($params['repo']['type'] == 'github') {
        $ex = 'git clone https://github.com/' . $params['repo']['name'];
    }

    $res = false;

    if ($ex) {
        $ex = "cd $rootDir & $ex $dot 2>&1";
        exec($ex, $res);
    }

    return $res;
}

function response($response)
{
    header('Content-type: application/json;');
    $json = json_encode($response, JSON_UNESCAPED_UNICODE);
    print $json;
    exit();
}

function unpackRepo__validate($params)
{
    if (!$params['repo']) {
        return [
            'status' => 'error',
            'errorData' => 'Params: repo required!',
        ];
    }

    if (strstr($params['login'], '@')) {
        return [
            'status' => 'error',
            'errorData' => 'Params: login shouldn\'t be email!',
        ];
    }
}

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
    <script
            src="https://code.jquery.com/jquery-3.6.0.min.js"
            integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4="
            crossorigin="anonymous"></script>
</head>
<body>


<div class="repoUnPacker">

    <form class="repoUnPacker__form js_repoUnPacker__form">

        <div class="repoUnPacker__form-title">
            <?= $message['title'] ?>
        </div>

        <div class="repoUnPacker__form-item">
            <div class="repoUnPacker__form-item-title">
                <?= $message['form']['fields']['linkToRepo'] ?> <span style="color: red;">*</span>
            </div>
            <label>
                <input value="<?= $_COOKIE['_deployGITR'] ?>" required autocomplete="off"
                       class="repoUnPacker__form-input" type="text" name="repo">
            </label>
        </div>

        <div class="repoUnPacker__form-item">
            <div class="repoUnPacker__form-item-title">
                <?= $message['form']['fields']['login'] ?>
            </div>
            <label>
                <input value="<?= $_COOKIE['_deployGITL'] ?>" autocomplete="off" class="repoUnPacker__form-input"
                       type="text" name="login">
            </label>
        </div>

        <div class="repoUnPacker__form-item">
            <div class="repoUnPacker__form-item-title">
                <?= $message['form']['fields']['password'] ?>
            </div>
            <label>
                <input value="<?= $_COOKIE['_deployGITP'] ?>" autocomplete="off" style="filter: blur(3px);"
                       class="repoUnPacker__form-input password"
                       type="text" name="password">
            </label>
        </div>

        <div class="repoUnPacker__form-item">
            <button class="repoUnPacker__form-button" type="submit"><?= $message['form']['buttons']['start'] ?></button>
        </div>

    </form>

    <div class="js_repoUnPacker__preloader">
        <svg width="80px" height="80px" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"
             preserveAspectRatio="xMidYMid" class="lds-ring" style="background: none;">
            <defs>
                <mask ng-attr-id="{{config.cpid}}" id="lds-flat-ring-a1b5111eb6e61">
                    <circle cx="50" cy="50" r="45" ng-attr-fill="{{config.base}}" fill="#e0e0e0"></circle>
                </mask>
            </defs>
            <circle cx="50" cy="50" r="45" ng-attr-fill="{{config.base}}" fill="#e0e0e0"></circle>
            <path ng-attr-d="{{config.d}}" ng-attr-fill="{{config.dark}}" ng-attr-mask="url(#{{config.cpid}})"
                  d="M26.66547622084393 73.33452377915606 A33 33 0 0 1 73.33452377915607 26.665476220843935 L173.3345237791561 126.66547622084394 L126.66547622084393 173.33452377915606 Z"
                  fill="rgb(181, 181, 181)" mask="url(#lds-flat-ring-a1b5111eb6e61)"></path>
            <circle cx="50" cy="50" ng-attr-r="{{config.radius}}" ng-attr-stroke="{{config.stroke2}}"
                    ng-attr-stroke-width="{{config.width}}" fill="none" r="28" stroke="#ffddc3"
                    stroke-width="10"></circle>
            <circle cx="50" cy="50" ng-attr-r="{{config.radius}}" ng-attr-stroke="{{config.stroke}}"
                    ng-attr-stroke-width="{{config.innerWidth}}" ng-attr-stroke-linecap="{{config.linecap}}" fill="none"
                    r="28" stroke="#ffffff" stroke-width="10" stroke-linecap="round" transform="rotate(6 50 50)">
                <animateTransform attributeName="transform" type="rotate" calcMode="linear"
                                  values="0 50 50;180 50 50;720 50 50" keyTimes="0;0.5;1" dur="1s" begin="0s"
                                  repeatCount="indefinite"></animateTransform>
                <animate attributeName="stroke-dasharray" calcMode="linear"
                         values="17.59291886010284 158.33626974092556;87.96459430051421 87.96459430051421;17.59291886010284 158.33626974092556"
                         keyTimes="0;0.5;1" dur="1" begin="0s" repeatCount="indefinite"></animate>
            </circle>
        </svg>
    </div>

    <div class="js_repoUnPacker__resp"></div>

</div>

<style>

    .password:focus {
        filter: blur(0) !important;
    }

    .repoUnPacker__form {
        width: 100%;
        max-width: 700px;
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

    .js_repoUnPacker__resp pre {
        padding: 20px;
        background: #333;
        color: #fff;
        width: fit-content;
        min-width: 660px;
        margin: 0 auto;
    }

    .js_repoUnPacker__preloader {
        text-align: center;
        margin-top: 50px;
        display: none;
    }

</style>
<script>
    $(function () {

        $(document).on('submit', '.js_repoUnPacker__form', function () {
            sendRequest($(this));
            return false;
        });

        function sendRequest(obj) {

            var data = new FormData;

            $.each(obj.serializeArray(), function (index, value) {
                data.append(value.name, value.value);
            });

            data.append('action', 'unpack');

            preload();
            $('.js_repoUnPacker__resp').html('');

            $.ajax({
                url: '',
                data: data,
                processData: false,
                contentType: false,
                type: 'POST',
                success: function (res) {

                    if (!res.data) {
                        res.data = res;
                    }

                    setTimeout(function () {
                        unPreload();
                        $('.js_repoUnPacker__resp').html(res.data);
                    }, 1000);

                }
            });

        }

        function preload() {
            $('body').css('pointer-events', 'none');
            $('.repoUnPacker__form-button').css({'opacity': '0.4'});
            $('.js_repoUnPacker__preloader').show();
            $('.repoUnPacker__form-input').css({'opacity': '0.6'});
        }

        function unPreload() {
            $('body').css('pointer-events', 'all');
            $('.repoUnPacker__form-button').css({'opacity': '1'});
            $('.js_repoUnPacker__preloader').hide();
            $('.repoUnPacker__form-input').css({'opacity': '1'});
        }

    });
</script>
</body>
</html>
