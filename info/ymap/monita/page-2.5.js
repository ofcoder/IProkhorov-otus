define(['jquery',
        'marionette',
        'text!templates/map/page-2.1.html',
        'views/problem_positions/providers',
        'models/statistic/filter_collection',
        'markerclusterer',
        'underscore'], function($, Marionette, MapTemplate, ProvidersView, ProvidersCollection, MarkerClustererLib, _) {
    return Marionette.View.extend({
        template: _.template(MapTemplate),
        chel_geo: [55.1461447,61.3838507],
        our_shops: [],
        all_our_shops: [],
        is_get_our_shops: false,
        marker_cluster: null,
        marker_cluster_our_shop: null,
        networks_shops: [],
        is_get_networks_shops: false,
        websocket: null,
        tracking: [],
        is_get_tracking: false,
        marker_cluster_tracking: null,
        info_window: null,
        tracking_timer: null,
        tracking_dev_stay: 0.0001,
        trackingClusterer: null,
        filter_region_id: 0,
        filter_city_id: 0,
        filter_shop_id: 0,
        sw_lat: 0,
        sw_lng: 0,
        ne_lat: 0,
        ne_lng: 0,
        center_lat: 0,
        center_lng: 0,

        view_all: false,

        page_hash: '#map',
        filters: {},

        ui: {
            filter_region: '.map-filter-region',
            filter_city: '.map-filter-city',
            filter_shop: '.map-filter-shop',
            filter_address: '.map-filter-address',
            filter_network: '.map-filter-network',
            filter_show_our_shop: '.map-filter-chk-our-shop',
            filter_show_networks: '.map-filter-chk-shops',
        },

        regions: {
            regions_area: '.map-filter-list-wrap.region',
            cities_area: '.map-filter-list-wrap.city',
            shops_area: '.map-filter-list-wrap.shop',
            networks_area: '.map-filter-list-wrap.network',
            address_area: '.map-filter-list-wrap.address',
        },

        events: {
            'click .map-filter-button': 'onClickFiltersShow',
            'click .map-filter-chk': 'onClickFilterSelector',
            'click .map-filter-region': 'onClickFilterRegion',
            'click .map-filter-city': 'onClickFilterCity',
            'click .map-filter-shop': 'onClickFilterShop',
            'click .map-filter-network': 'onClickFilterNetwork',
            'click .map-filter-address': 'onClickFilterAddress',
            'click .map-filter-clean': 'onClickClean',
            'click .map-filter-show': 'onClickShow'
        },

        initialize: function()
        {
            var t = this;
            this.on('window:resize', function(){
                t.onResizeWindow();
            });

            this.on('body:click', function(){
                $.each(t.getRegions(), function(index, region) {
                    if (region.currentView && region.currentView.hideList)
                        region.currentView.hideList();
                });
            });
        },

        onRender: function()
        {
            $('.map-map').show();
            $('.modal-spinner').show();

            this.view_all = $.inArray('tracking_full', window.user.permissions) != -1

            var t = this;
            const ymapsBoundsChange = async (event) => {
                const newBounds = await event.get('newBounds');
                const v_sw_lat = newBounds[0][0];
                const v_sw_lng = newBounds[0][1];
                const v_ne_lat = newBounds[1][0];
                const v_ne_lng = newBounds[1][1];
                const lat_additive = (v_ne_lat - v_sw_lat)/2;
                const lng_additive = (v_ne_lng - v_sw_lng)/2;
                const bounds = window.geo_boundaries;
                const boundsOld = {
                    sw_lat:bounds.sw_lat,
                    sw_lng:bounds.sw_lng,
                    ne_lat:bounds.ne_lat,
                    ne_lng:bounds.ne_lng                  
                }                

                if (
                    v_sw_lat < bounds.sw_lat - lat_additive ||
                    v_sw_lat > bounds.ne_lat + lat_additive ||
                    v_ne_lat < bounds.sw_lat - lat_additive ||
                    v_ne_lat > bounds.ne_lat + lat_additive ||
                    v_sw_lng < bounds.sw_lng - lng_additive ||
                    v_sw_lng > bounds.ne_lng + lng_additive ||
                    v_ne_lng < bounds.sw_lng - lng_additive ||
                    v_ne_lng > bounds.ne_lng + lng_additive
                ) {
                    bounds.sw_lat = v_sw_lat - lat_additive;
                    bounds.sw_lng = v_sw_lng - lng_additive;
                    bounds.ne_lat = v_ne_lat + lat_additive;
                    bounds.ne_lng = v_ne_lng + lng_additive;
                    if (bounds.sw_lat !=0 && bounds.sw_lng !=0 && bounds.ne_lat !=0 && bounds.ne_lng !=0) {
                        t.getAllShops();
                        t.getGeopoints();
                        t.restoreFilters();
                        t.DrawMapObjects();                        
                    }
                }
            };

            this.showRegions();
            this.showNetworks();

            this.onResizeWindow();
            if (!window.map_map) {
                ymaps.ready(function () {
                    window.map_map = new ymaps.Map($('.map-map')[0], {
                        center: [55.76, 37.64],
                        zoom: 11,
                        controls: [],
                    }, {
                        yandexMapDisablePoiInteractivity: true
                    });
                    window.map_map.events.add('boundschange', ymapsBoundsChange);
                });
            }
            // Try W3C Geolocation (Preferred)
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function (position) {
                    ymaps.ready(function () {
                        window.map_map.setCenter([position.coords.latitude, position.coords.longitude]);
                    });
                }, function () {
                    ymaps.ready(function () {
                        window.map_map.setCenter([t.chel_geo[0], t.chel_geo[1]]);
                    });
                });
            }
            // Browser doesn't support Geolocation
            else {
                ymaps.ready(function () {
                    window.map_map.setCenter([t.chel_geo[0], t.chel_geo[1]]);
                });
            }
            this.getAllShops();
            this.getGeopoints();
            this.restoreFilters();
        },

        onDestroy: function()
        {
            if (this.marker_cluster_our_shop)
                 window.map_map.geoObjects.removeAll();

            if (this.marker_cluster)
                for (var i = 0; i < this.marker_cluster.length; i++)
                    window.map_map.geoObjects.remove(this.marker_cluster[i]);


            if (this.marker_cluster_tracking)
            {
                var markers = this.marker_cluster_tracking.getMarkers();
                for (var i = 0; i < markers.length; i++)
                    window.map_map.geoObjects.remove(markers[i]);
            }

            if (this.tracking_timer)
                clearInterval(this.tracking_timer);

            $('.map-map').hide();
        },

        onResizeWindow: function()
        {
            if ($.fn.mobile_check())
                $('.map-map').css({
                    height: $(window).height() - $('.navigation-bar').height() + 'px',
                    width: $(window).width() + 'px',
                    top: $('.navigation-bar').height() + 'px'
                });
            else
                $('.map-map').css({
                    height: $(window).height() + 'px',
                    width: $(window).width() - $('.menu').width() + 'px',
                    left: $('.menu').width() + 'px'
                });
        },

        find_tracking_pos_binary: function(phone)
        {
            var lo = 0,
                hi = this.tracking.length - 1,
                mid;
            while (lo <= hi) {
                mid = Math.floor((lo + hi) / 2);
                if (this.tracking[mid].phone > phone)
                    hi = mid - 1;
                else if (this.tracking[mid].phone < phone)
                    lo = mid + 1;
                else
                    return mid;
            }

            if (lo >= this.tracking.length)
                return lo;
            else if (phone > this.tracking[lo].phone)
                return lo + 1;
            else
                return lo;
        },

        getGeopoints: function()
        {
            var t = this;
            var Radio = require('backbone.radio');
            var channel = Radio.channel('shops');
            channel.replyOnce('geopoints:answer', function(geopoints) {
                t.networks_shops = geopoints;
                t.is_get_networks_shops = true;
                if ($.isEmptyObject(t.filters))
                    t.DrawMapObjects();
            });
            channel.request('geopoints:get');
        },

        getAllShops: function()
        {
            var t = this;
            var Radio = require('backbone.radio');
            var channel = Radio.channel('shops');
            channel.replyOnce('all_shops:answer', function(shops) {
                t.our_shops = shops;
                t.is_get_our_shops = true;
                if ($.isEmptyObject(t.filters))
                    t.DrawMapObjects();
            });
            channel.request('all_shops:get');
        },

        getTracking: function()
        {
            var t = this;
            const shop_id = +this.ui.filter_shop.attr('data-id');
            if (!shop_id) {
                requirejs(['alerts'], function(Alerts) {
                    Alerts.showAlertError('Выберите наш магазин!');
                });
                return;                
            }
            $.fn.app_ajax({
                url: 'tracking/last?shop_id=' + shop_id.toString(),
                request_type: 'GET',
                content_type: 'application/json',
                data_type: 'json',
                skip_result_success: true,
                success_func: function(data) {
                    t.tracking = data;
                    const name = 'Мониторщик';
                    t.tracking.map(tr => {tr.id = tr.employee_id, tr.name = tr.employee_name ?? name, tr.position = 'Мониторщик'}); 
                    t.DrawMapObjects();
                }
            });
        },

        onClickFiltersShow: function()
        {
            $('.map-filters').toggle();
        },

        onClickFilterSelector: function(e)
        {
            var elem = $(e.currentTarget);
            if (elem.hasClass('on'))
                elem.addClass('off').removeClass('on');
            else
                elem.addClass('on').removeClass('off');
        },

        onClickFilterRegion: function(e)
        {
            this.onClickFilter(e, 'regions_area');
        },

        onClickFilterCity: function(e)
        {
            this.onClickFilter(e, 'cities_area');
        },

        onClickFilterShop: function(e)
        {
            this.onClickFilter(e, 'shops_area');
        },

        onClickFilterNetwork: function(e)
        {
            this.onClickFilter(e, 'networks_area');
        },

        onClickFilterAddress: function(e)
        {
            this.onClickFilter(e, 'address_area');
        },

        onClickFilter: function(e, region_name)
        {
            if ($(e.currentTarget).hasClass('disable'))
                return;
            e.stopPropagation();
            var is_visible = this.getRegion(region_name).currentView.isShowingList();
            $.each(this.getRegions(), function(index, region) {
                if (region.currentView && region.currentView.hideList)
                    region.currentView.hideList();
            });

            if (!is_visible)
                this.getRegion(region_name).currentView.showList();
        },

        showRegions: function()
        {
            var t = this;
            var Radio = require('backbone.radio');
            var channel = Radio.channel('shops');
            channel.replyOnce('regions:answer', function(regions) {
                requirejs(['views/statistic/filter'], function(FilterView)
                {
                    var view = new FilterView({collection: regions, parent_elem: t.ui.filter_region});
                    view.on('select', function(view){
                        t.showCities(view.model.get('id'));
                        t.ui.filter_city.addClass('disable').attr('data-id', 0).text('Все');
                        t.ui.filter_shop.addClass('disable').attr('data-id', 0).text('Все');
                        if (t.getRegion('cities_area').currentView)
                            t.getRegion('cities_area').currentView.clearFilterText();
                        if (t.getRegion('shops_area').currentView)
                            t.getRegion('shops_area').currentView.clearFilterText();
                        t.ui.filter_address.addClass('disable').attr('data-id', 0).text('Все');
                        t.showGeoPoints();
                        if (t.getRegion('address_area').currentView)
                            t.getRegion('address_area').currentView.clearFilterText();
                    });
                    t.showChildView('regions_area', view);
                    if (t.filters.region_id)
                    {
                        view.select(+t.filters.region_id);
                        delete t.filters.region_id;
                    }
                });
            });
            channel.request('regions:get');
        },

        showCities: function (region_id) {
            var t = this;
            var Radio = require('backbone.radio');
            var channel = Radio.channel('shops');
            channel.replyOnce('cities:answer', function (cities) {
                requirejs(['views/statistic/filter'], function (FilterView) {
                    var view = new FilterView({
                        collection: cities,
                        parent_elem: t.ui.filter_city,
                    });
                    view.on('select', function (current_view) {
                        t.showShops(current_view.model.get('id'));
                        const cityAttributes =
                            current_view.options.model.attributes;
                        if (
                            window.map_map &&
                            cityAttributes.southwest_lat &&
                            cityAttributes.southwest_lng &&
                            cityAttributes.northeast_lat &&
                            cityAttributes.northeast_lng
                        ) {
                            window.map_map.setBounds([
                                [
                                    cityAttributes.southwest_lat,
                                    cityAttributes.southwest_lng,
                                ],
                                [
                                    cityAttributes.northeast_lat,
                                    cityAttributes.northeast_lng,
                                ],
                            ]);
                        }
                        if (t.getRegion('regions_area').currentView)
                            t.getRegion(
                                'regions_area'
                            ).currentView.clearFilterText();

                        if (t.getRegion('shops_area').currentView) {
                            t.getRegion(
                                'shops_area'
                            ).currentView.clearFilterText();
                            if (
                                !current_view.model.id &&
                                +t.ui.filter_region.attr('data-id')
                            )
                                t.getRegion(
                                    'shops_area'
                                ).currentView.filter_data(
                                    'region_id',
                                    +t.ui.filter_region.attr('data-id')
                                );
                            else
                                t.getRegion(
                                    'shops_area'
                                ).currentView.filter_data(
                                    'city_id',
                                    current_view.model.id
                                );
                        }
                        t.ui.filter_shop.attr('data-id', 0).text('Все');
                        t.ui.filter_address
                            .addClass('disable')
                            .attr('data-id', 0)
                            .text('Все');
                        t.showGeoPoints();
                        if (t.getRegion('address_area').currentView)
                            t.getRegion(
                                'address_area'
                            ).currentView.clearFilterText();
                    });
                    t.showChildView('cities_area', view);
                    if (t.filters.city_id) {
                        view.select(+t.filters.city_id);
                        delete t.filters.city_id;
                    }
                });
            });
            channel.request('cities:get', region_id);
        },

        showShops: function(city_id)
        {
            if (city_id == 0)
                return;

            var t = this;
            var Radio = require('backbone.radio');
            var channel = Radio.channel('shops');
            channel.replyOnce('shops:answer', function(shops) {
                t.our_shops = shops;
                t.all_our_shops = shops.filter(shop => shop.id !== 0);
                requirejs(['views/statistic/filter'], function(FilterView)
                {
                    for (var i = 0; i < shops.length; i++)
                        shops[i].name = shops[i].address + (shops[i].id ? ' (' + shops[i].id + ')' : '');
                    var view = new FilterView({collection: shops, parent_elem: t.ui.filter_shop, full_search: true});
                    t.showChildView('shops_area', view);
                    if (t.filters.shop_id)
                    {
                        view.select(+t.filters.shop_id);
                        delete t.filters.shop_id;
                    }
                });
            });
            channel.request('shops:get', city_id);
        },

        showNetworks: function()
        {
            var t = this;
            var Radio = require('backbone.radio');
            var channel = Radio.channel('shops');
            channel.replyOnce('networks:answer', function(networks) {
                requirejs(['views/statistic/filter'], function(FilterView)
                {
                    var view = new FilterView({collection: networks, parent_elem: t.ui.filter_network, full_search: true});
                    view.on('select', function(view){
                        t.ui.filter_address.addClass('disable').attr('data-id', 0).text('Все');
                        t.showGeoPoints();
                        if (t.getRegion('address_area').currentView)
                            t.getRegion('address_area').currentView.clearFilterText();
                    });
                    t.showChildView('networks_area', view);
                    if (t.filters.network_id)
                    {
                        view.select(+t.filters.network_id);
                        delete t.filters.network_id;
                    }
                });
            });
            channel.request('networks:get');
        },

        showGeoPoints: function()
        {
            var shop_network_id = +this.ui.filter_network.attr('data-id'),
                region_id = +this.ui.filter_region.attr('data-id'),
                city_id = +this.ui.filter_city.attr('data-id');

            if (!shop_network_id)
                return;

            var t = this;
            var Radio = require('backbone.radio');
            var channel = Radio.channel('shops');
            channel.replyOnce('geopoints_address:answer', function(geo_points) {
                requirejs(['views/statistic/filter'], function(FilterView)
                {
                    var view = new FilterView({collection: geo_points, parent_elem: t.ui.filter_address, full_search: true});
                    t.showChildView('address_area', view);
                    if (t.filters.geopoint_id)
                    {
                        view.select(+t.filters.geopoint_id);
                        delete t.filters.geopoint_id;
                    }
                });
            });
            channel.request('geopoints_address:get', {shop_network_id: shop_network_id, region_id: region_id, city_id: city_id});
        },

        onClickClean: function()
        {
            this.ui.filter_region.text('Все').attr('data-id', 0);
            this.ui.filter_network.text('Все').attr('data-id', 0);
            this.ui.filter_address.text('Все').attr('data-id', 0).addClass('disable');
            this.ui.filter_city.text('Все').attr('data-id', 0).addClass('disable');
            this.ui.filter_shop.text('Все').attr('data-id', 0).addClass('disable');
            $.each(this.getRegions(), function(index, region) {
                if (region.currentView && region.currentView.clearFilterText)
                    region.currentView.clearFilterText();
            });
        },

        DrawMapObjects: function()
        {
            if (!this.is_get_our_shops || !this.is_get_networks_shops) {
                return;
            }

            var is_show_our_shops = this.ui.filter_show_our_shop.hasClass('on'),
                is_show_networks_shops = this.ui.filter_show_networks.hasClass('on'),
                region_id = +this.ui.filter_region.attr('data-id'),
                city_id = +this.ui.filter_city.attr('data-id'),
                shop_id = +this.ui.filter_shop.attr('data-id'),
                network_id = +this.ui.filter_network.attr('data-id'),
                geopoint_id = +this.ui.filter_address.attr('data-id')

            this.filter_region_id = region_id;
            this.filter_city_id = city_id;
            this.filter_shop_id = shop_id;

            var markers = this.GetDrawOurShops(is_show_our_shops, region_id, city_id, shop_id);
            this.DrawOurShopsMarkers(markers);
            markers = this.GetDrawNetworkShops(is_show_networks_shops, region_id, city_id, shop_id, network_id, geopoint_id);
            this.DrawShopsMarkers(markers);
            this.DrawTrackingMarkers(region_id, city_id, shop_id);

            $('.modal-spinner').hide();
        },

        GetDrawOurShops: function(is_show_our_shops)
        {
            if (!is_show_our_shops)
                return [];

            var t = this,
                markers = [];
            const shops = Array.isArray(t.all_our_shops) && t.all_our_shops.length > 0 ? t.all_our_shops : [];
            shops.map(shop => {
                    const iconContentLayout = ymaps.templateLayoutFactory.createClass(
                        '<div class="map-info-shop">' +
                        '<div class="map-info-shop-info">' +
                        '<p class="map-info-shop-name">Наш магазин</p>' +
                        '<p class="map-info-shop-address">' + shop.address + '</p>' +
                        '</div>' + '</div>',
                    ); 
                    const placeMark = new ymaps.Placemark([shop.lat, shop.lng], {
                         balloonContent: shop.address,
                    }, {
                        iconLayout: 'default#imageWithContent',
                        iconImageHref: 'imgs/markers/our-shop.png',
                        iconImageSize: [25, 33],
                        iconImageOffset: [-5, -38],
                        iconContentLayout: iconContentLayout,
                    });
                    markers.push(placeMark);                                   
            })
            return markers;
        },

        DrawOurShopsMarkers: function(markers)
        {
             // remove our shops and network shops markers from map
            if (markers.length >= 0) {
                window.map_map.geoObjects.removeAll();
            }
            ymaps.ready(function () {
                iconContentLayout = ymaps.templateLayoutFactory.createClass(
                    '<div style="color: #FFFFFF; font-weight: bold;">{{ properties.geoObjects.length }}</div>',
                );
                clusterer = new ymaps.Clusterer({
                    clusterIcons: [
                        {
                            href:  'imgs/markers/our-shop.png',
                            size: [25, 33],
                            offset: [-30, -30],
                        },
                    ],
                    clusterNumbers: [30],
                    clusterIconContentLayout: iconContentLayout,
                });
                getPointData = function (index, address) {
                    return {
                        balloonContentBody: '<div class="map-info-shop">' +
                        '<div class="map-info-shop-info">' +
                        '<p class="map-info-shop-name">Наш магазин</p>' +
                        '<p class="map-info-shop-address">' + address + '</p>' +
                        '</div>' + '</div>',
                    };
                }
                points = [];
                for (let i = 0; i < markers.length; i++) {
                    points.push([markers[i].geometry._coordinates[0], markers[i].geometry._coordinates[1]]);
                }
                myGeoObjects = [];
                for (let i = 0, len = points.length; i < len; i++) {
                    myGeoObjects[i] = new ymaps.Placemark(points[i],
                        getPointData(i, markers[i].properties._data.balloonContent), {
                            iconLayout: 'default#imageWithContent',
                            iconImageHref: 'imgs/markers/our-shop.png',
                            iconImageSize: [25, 33],
                            iconImageOffset: [-5, -38],
                            hideIconOnBalloonOpen: false,
                            balloonOffset: [5, -35],
                        });
                }
                clusterer.add(myGeoObjects);
                window.map_map.geoObjects.add(clusterer);
            });

        },

        GetDrawNetworkShops: function(is_show_networks_shops, region_id, city_id, shop_id, network_id, geopoint_id)
        {
            var markers = [];
            if (!is_show_networks_shops)
                return markers;

            var t = this;

            for (var i = 0; i < t.networks_shops.length; i++) {
                if ((shop_id != 0 || region_id == 0 || region_id == t.networks_shops[i].region_id) &&
                    (shop_id != 0 || city_id == 0 || city_id == t.networks_shops[i].city_id) &&
                    (shop_id == 0 || t.check_shop_id(t.networks_shops[i].shops, shop_id)) &&
                    (network_id == 0 || network_id == t.networks_shops[i].shop_network_id) &&
                    (geopoint_id == 0 || geopoint_id == t.networks_shops[i].id))
                {
                    let marker = new ymaps.Placemark([t.networks_shops[i].lat, t.networks_shops[i].lng], {
                        balloonContent: t.networks_shops[i].id,
                    }, {
                        iconLayout: 'default#imageWithContent',
                        iconImageHref: (t.networks_shops[i].icon) ? t.networks_shops[i].icon : 'imgs/markers/default.png',
                    });

                    // find network in markers
                    var indx = -1;
                    for (var j = 0; j < markers.length; j++)
                    {
                        if (markers[j].shop_network_id == t.networks_shops[i].shop_network_id)
                        {
                            indx = j;
                            break;
                        }
                    }

                    if (indx == -1)
                    {
                        markers.push({
                            shop_network_id: t.networks_shops[i].shop_network_id,
                            name: t.networks_shops[i].name,
                            icon: (t.networks_shops[i].icon) ? t.networks_shops[i].icon : 'imgs/markers/default.png',
                            markers: []
                        });
                        indx = markers.length - 1;
                    }

                    markers[indx].markers.push(marker);
                }
            }

            return markers;
        },

        DrawShopsMarkers: async function(markers)
        {
            if (markers.length >= 0) {
                window.map_map.geoObjects.removeAll();
            }
            // remove our shops and network shops markers from map
            if (this.marker_cluster)
                for (let k = 0; k < this.marker_cluster.length; k++)
                    this.marker_cluster[k].clearMarkers();

            this.marker_cluster = [];
            for await (const marker of markers)
            {
               ymaps.ready(function () {
                    clustererCompetitors = new ymaps.Clusterer({
                        clusterIcons: [
                            {
                                href:  marker.icon,
                                size: [32, 31],
                                offset: [-25, -25],
                            }
                        ],
                        clusterNumbers: [30],
                        clusterIconContentLayout: null,
                    });

                    points = [];
                    for (var i = 0; i < marker.markers.length; i++) {
                        points.push([marker.markers[i].geometry._coordinates[0], marker.markers[i].geometry._coordinates[1]]);
                    }
                    geoObjects = [];
                    for (var i = 0, len = points.length; i < len; i++) {
                        geoObjects[i] = new ymaps.Placemark(points[i], {}, {
                                iconLayout: 'default#imageWithContent',
                                iconImageHref: marker.icon,
                                iconImageSize: [32, 31],
                                iconImageOffset: [-10, -30],
                                hideIconOnBalloonOpen: false,
								balloonOffset: [0, -25],
                        });
                        let markerId = marker.markers[i].properties._data.balloonContent;
                        let geoObj = geoObjects[i];

                        geoObjects[i].events.add('click', function (event) {
                            $.fn.app_ajax({
                                url: 'geoshops/?id=' + markerId,
                                request_type: 'GET',
                                success_func: function(response) {
                                    let data = response.values[0];
                                    geoObj.properties._data.balloonContentBody =
                                        '<div class="map-info-shop">' +
                                            '<img class="map-info-shop-img" src="' + (data.shop_network_logo ? data.shop_network_logo : '') + '">' +
                                            '<div class="map-info-shop-info">' +
                                                '<p class="map-info-shop-name">' + data.shop_network_name + '</p>' +
                                            '</div>' +
                                            '<div class="clearFix"></div>' +
                                            '<p class="map-info-shop-address">' + data.address + '</p>' +
                                            '<p class="map-info-shop-creator">Создатель метки:</p>' +
                                            '<p class="map-info-shop-user">' + data.created_by.name + '</p>' +
                                            '<p class="map-info-shop-creator">Дата создания:</p>' +
                                            '<p class="map-info-shop-user">' + data.created + '</p>' +
                                        '</div>';
                                    event.get('target').options.set('balloonContentBody', geoObj.properties._data.balloonContentBody);
                                }
                            })
                            geoObj.properties._data.balloonContentBody =
                                '<div class="map-info-shop">' +
                                    '<div class="map-info-loader"></div>' +
                                '</div>';
                            event.get('target').options.set('balloonContentBody', geoObj.properties._data.balloonContentBody);  
                        });
                    }
                    clustererCompetitors.add(geoObjects);
                    window.map_map.geoObjects.add(clustererCompetitors);
                });
            }
        },

        DrawTrackingMarkers: async function()
        {
            var t = this;

            ymaps.ready(function () {
                clusterer = new ymaps.Clusterer({
                    gridSize: 64,
                    groupByCoordinates: false,
                    hasBalloon: false,
                    hasHint: false,
                    margin: 10,
                    maxZoom: 14,
                    showInAlphabeticalOrder: false,
                    viewportMargin: 128,
                    zoomMargin: 0,
                    clusterDisableClickZoom: false,
                });

                t.trackingClusterer = clusterer;
                const geoObjects = [];

                t.tracking
                    .map((track, i) => {
                        const markerSize = 19;
                        let BalloonContentBody = '';
                        if (track.photo_url) {
                            BalloonContentBody =
                            '<div class="map-info-user">' +
                                '<div class="map-info-user-img-wrap">' +
                                    '<div class="map-info-user-img" style="background:url(' + track.photo_url + ') center center no-repeat">' + '</div>' +
                                '</div>' + 
                                '<div class="map-info-user-info">' +
                                    '<p class="map-info-user-name">' + track.name + '</p>' +
                                    '<p class="map-info-user-app">' + track.position + '</p>' +
                                    '<p class="map-info-user-phone">' + track.phone + '</p>' +
                                    '<p class="map-info-user-email">' +
                                        '<a class="map-info-user-email-a">' + track.email + '</a>' +
                                    '</p>' +
                                '</div>' +
                                '<div class="clearFix">' + '</div>' +
                            '</div>';                            
                        } else {
                            BalloonContentBody =
                            '<div class="map-info-user">' +
                                '<div class="map-info-user-info-without-photo">' +
                                    '<p class="map-info-user-name">' + track.name + '</p>' +
                                    '<p class="map-info-user-app">' + track.position + '</p>' +
                                    '<p class="map-info-user-phone">' + track.phone + '</p>' +
                                    '<p class="map-info-user-email">' +
                                        '<a class="map-info-user-email-a">' + track.email + '</a>' +
                                    '</p>' +
                                '</div>' +
                                '<div class="clearFix">' + '</div>' +
                            '</div>';
                        }
                        const IconContentLayout = ymaps.templateLayoutFactory.createClass(
                            '<div style="padding: ' + markerSize +'px 0 0 ' + markerSize + 'px;">' +
                            '<p class="map-marker-hint-app">' + track.name + '</p>' +
                            '<p class="map-marker-hint-dt">' + t.format_dt(track.datetime) + '</p>' +
                            '</div>',
                        );
                        const trackingMarker = new ymaps.Placemark([track.lat, track.lng], {
                            id: track.id,
                            name: track.name,
                            department_id: track.department_id,
                            email: track.email,
                            phone: track.phone,
                            position: track.position,
                            balloonContentBody: BalloonContentBody,
                        }, {
                            iconLayout: 'default#imageWithContent',
                            iconImageHref: 'imgs/map-stop.png',
                            iconImageSize: [markerSize,markerSize],
                            iconImageOffset: [-markerSize, -markerSize],
                            balloonOffset: [-markerSize/2, -markerSize],
                            iconContentLayout: IconContentLayout,
                            hideIconOnBalloonOpen: false,
                            openEmptyBalloon: true,
                            openBalloonOnClick: true,
                        });
                        geoObjects.push(trackingMarker);

                    });

                clusterer.add(geoObjects);
                window.map_map.geoObjects.add(clusterer);
            });
        },

        create_tracking_marker: function(tracking)
        {
            var t = this;
        },

        form_phone: function(phone)
        {
            if (!phone)
                return '';

            phone = String(phone);
            return '+' + phone.substr(0, 1) + ' ' + phone.substr(1, 3) + ' ' + phone.substr(4, 3) + ' ' + phone.substr(7);
        },

        check_shop_id: function(shops, shop_id)
        {
            if (!shops)
                return false;

            for (var i = 0; i < shops.length; i++)
            {
                if (shops[i] == shop_id)
                    return true;
            }
            return false;
        },

        format_dt: function(dt)
        {
            var dd = new Date(dt),
                d = dd.getDate() < 10 ? '0' + dd.getDate() : dd.getDate(),
                m = dd.getMonth() < 9 ? '0' + (dd.getMonth() + 1) : dd.getMonth() + 1,
                y = dd.getFullYear(),
                h = dd.getHours() < 10 ? '0' + dd.getHours() : dd.getHours(),
                min = dd.getMinutes() < 10 ? '0' + dd.getMinutes() : dd.getMinutes(),
                s = dd.getSeconds() < 10 ? '0' + dd.getSeconds() : dd.getSeconds();
            return d + '.' + m + '.' + y + ' ' + h + ':' + min + ':' + s;
        },

        onClickShow: function()
        {
            this.saveFilters();
            // this.DrawMapObjects();
            this.getTracking();
            if (navigator.userAgent.indexOf('iPhone') != -1)
                $('.map-filters').hide();
        },

        saveFilters: function()
        {
            var filters = {
                is_show_our_shops: this.ui.filter_show_our_shop.hasClass('on') ? '1' : '0',
                is_show_networks_shops: this.ui.filter_show_networks.hasClass('on') ? '1' : '0',
                region_id: +this.ui.filter_region.attr('data-id'),
                city_id: +this.ui.filter_city.attr('data-id'),
                shop_id: +this.ui.filter_shop.attr('data-id'),
                network_id: +this.ui.filter_network.attr('data-id'),
                geopoint_id: +this.ui.filter_address.attr('data-id'),
            };

            $.fn.make_filter_string(filters, this.page_hash);
        },

        restoreFilters: function()
        {
            this.filters = $.fn.parse_filter_string();

            if (this.filters.is_show_our_shops)
            {
                if (+this.filters.is_show_our_shops)
                {
                    this.ui.filter_show_our_shop.addClass('on');
                    this.ui.filter_show_our_shop.removeClass('off');
                }
                else
                {
                    this.ui.filter_show_our_shop.removeClass('on');
                    this.ui.filter_show_our_shop.addClass('off');
                }
                delete this.filters.is_show_our_shops;
            }

            if (this.filters.is_show_networks_shops)
            {
                if (+this.filters.is_show_networks_shops)
                {
                    this.ui.filter_show_networks.addClass('on');
                    this.ui.filter_show_networks.removeClass('off');
                }
                else
                {
                    this.ui.filter_show_networks.removeClass('on');
                    this.ui.filter_show_networks.addClass('off');
                }
                delete this.filters.is_show_networks_shops;
            }

            this.filter_region_id = this.filters.region_id ?? 0;
            this.filter_city_id = this.filters.city_id ?? 0;
            this.filter_shop_id = this.filters.shop_id ?? 0;
            if (this.filter_shop_id) {
                var t = this;
                var timer = setInterval(function(){
                    if ($.isEmptyObject(t.filters))
                    {
                        clearInterval(timer);
                        const shop_id = t.ui.filter_shop.attr('data-id');
                        if (shop_id) {
                           t.getTracking(); 
                        }
                    }
                }, 200);                
            }
        }
    });
});
