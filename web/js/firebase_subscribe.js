// firebase_subscribe.js
firebase.initializeApp({
    messagingSenderId: '649508349157'
});

// браузер поддерживает уведомления
// вообще, эту проверку должна делать библиотека Firebase, но она этого не делает
if ('Notification' in window) {
    var messaging = firebase.messaging();

    // пользователь уже разрешил получение уведомлений
    // подписываем на уведомления если ещё не подписали
    if (Notification.permission === 'granted') {
        subscribe();
    }

    // по клику, запрашиваем у пользователя разрешение на уведомления
    // и подписываем его
    $('#subscribe').on('click', function () {
        subscribe();
    });

    messaging.onMessage(function(payload) {

        // регистрируем пустой ServiceWorker каждый раз
        navigator.serviceWorker.register('firebase-messaging-sw.js');
        navigator.serviceWorker.ready.then(function(registration) {
            // теперь мы можем показать уведомление
            var data = JSON.parse(payload.data.notification);
            return registration.showNotification(data.title, {
                body: data.body,
                icon: data.icon,
                click_action: data.click_action,
                //time_to_live: data.time_to_live,
                data: { url: data.click_action },
                tag: data.tag
            });
        }).catch(function(error) {
            console.log('ServiceWorker registration failed', error);
        });
    });
}

function subscribe() {
    // запрашиваем разрешение на получение уведомлений
    messaging.requestPermission()
        .then(function () {
            // получаем ID устройства
            //проверяем сначала, может он уже получен и установлен для пользователя
            var url = '/index.php?r=settings%2Fget-firebase-token'; // адрес скрипта на сервере который возвращает ID устройства
            if ($('#login-form').length == 0 && $('#register-form').length == 0) //если мы не логинимся и не регаемся
            {
                $.get(url, function( data ) {
                    if(data.data != null) //сохраняем полученный токен
                    {
                        setTokenSentToServer(data.data);
                        console.log(data.data);
                    }
                    else {
                        //генерируем новый
                        messaging.getToken()
                            .then(function (currentToken) {
                                console.log(currentToken);

                                if (currentToken) {
                                    sendTokenToServer(currentToken);
                                } else {
                                    console.warn('Не удалось получить токен.');
                                    setTokenSentToServer(false);
                                }
                            })
                            .catch(function (err) {
                                console.warn('При получении токена произошла ошибка.', err);
                                setTokenSentToServer(false);
                            });
                    }
                });
            }
        })
        .catch(function (err) {
            console.warn('Не удалось получить разрешение на показ уведомлений.', err);
        });
}

// отправка ID на сервер
function sendTokenToServer(currentToken) {
    //if (!isTokenSentToServer(currentToken)) {
        console.log('Отправка токена на сервер...');

        var url = '/index.php?r=settings%2Fset-firebase-token'; // адрес скрипта на сервере который сохраняет ID устройства
        $.post(url, {
            token: currentToken
        });

        setTokenSentToServer(currentToken);
    /*} else {
        console.log('Токен уже отправлен на сервер.');
    }*/
}

// используем localStorage для отметки того,
// что пользователь уже подписался на уведомления
function isTokenSentToServer(currentToken) {
    return window.localStorage.getItem('sentFirebaseMessagingToken') == currentToken;
}

function setTokenSentToServer(currentToken) {
    window.localStorage.setItem(
        'sentFirebaseMessagingToken',
        currentToken ? currentToken : ''
    );
}