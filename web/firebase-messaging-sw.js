// firebase-messaging-sw.js
importScripts('https://www.gstatic.com/firebasejs/3.6.8/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/3.6.8/firebase-messaging.js');

firebase.initializeApp({
    messagingSenderId: '649508349157'
});

const messaging = firebase.messaging();

messaging.setBackgroundMessageHandler(function (payload) {
    var data = JSON.parse(payload.data.notification);
    self.registration.showNotification(data.title, {
        body: data.body,
        icon: data.icon,
        click_action: data.click_action,
        time_to_live: data.time_to_live,
        data: {url: data.click_action},//data.data,
        tag: data.tag
    });
});

self.addEventListener('notificationclick', function(event) {
    let url = 'https://google.com/';
    event.notification.close(); // Android needs explicit close.
    event.waitUntil(
        clients.matchAll({type: 'window'}).then( windowClients => {
            // Check if there is already a window/tab open with the target URL
            for (var i = 0; i < windowClients.length; i++) {
                var client = windowClients[i];
                // If so, just focus it.
                if (client.url === url && 'focus' in client) {
                    return client.focus();
                }
            }
            // If not, then open the target URL in a new window/tab.
            if (clients.openWindow) {
                return clients.openWindow(url);
            }
        })
    );
});

