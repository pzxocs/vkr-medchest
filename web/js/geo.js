function init() {
    var map;

    var address = $("#address_input").val();
    if(address) //если указан адрес, то ищем по нему
    {
        var geocoder = ymaps.geocode(address);

        geocoder.then(
            function (res) {

                var position = res.geoObjects.get(0);
                if(position)
                {
                    // координаты объекта
                    var coordinates = res.geoObjects.get(0).geometry.getCoordinates();

                    var mapContainer = $('#map'),
                        bounds = position.properties.get('boundedBy'),
                        // Рассчитываем видимую область для текущей положения пользователя.
                        mapState = ymaps.util.bounds.getCenterAndZoom(
                            bounds,
                            [mapContainer.width(), mapContainer.height()]
                        );

                    // Добавление метки (Placemark) на карту
                    var placemark = new ymaps.Placemark(
                        coordinates, {
                            'hintContent': address,
                            'balloonContent': 'Вы здесь'
                        }, {
                            'preset': 'islands#redDotIcon'
                        }
                    );
                    createMap(mapState, placemark);
                }
                else
                {
                    useGeolocation();
                }

            }, function (e) {
                // Если местоположение невозможно найти
                useGeolocation();
            });
    }
    else //если нет - геолокация
    {
        useGeolocation();
    }

    function useGeolocation() {
        ymaps.geolocation.get().then(function (res) {
            var mapContainer = $('#map'),
                bounds = res.geoObjects.get(0).properties.get('boundedBy'),
                // Рассчитываем видимую область для текущей положения пользователя.
                mapState = ymaps.util.bounds.getCenterAndZoom(
                    bounds,
                    [mapContainer.width(), mapContainer.height()]
                );
            createMap(mapState);
        }, function (e) {
            // Если местоположение невозможно получить, то просто создаем карту.
            createMap({
                center: [55.751574, 37.573856],
                zoom: 2
            });
        });
    }

    function createMap (state, placemark) {
        map = new ymaps.Map('map', state);

        // Создадим экземпляр элемента управления «поиск по карте»
        // с установленной опцией провайдера данных для поиска по организациям.
        var searchControl = new ymaps.control.SearchControl({
            options: {
                provider: 'yandex#search'
            }
        });

        if(placemark != null)
        {
            map.geoObjects.add(placemark);
        }

        map.controls.add(searchControl);

        // Программно выполним поиск определённых аптек в текущей
        // прямоугольной области карты.
        searchControl.search('Аптеки');
    }
}

ymaps.ready(init);
