const { event } = require('jquery');

define(['jquery',
  'marionette',
  'text!templates/geopoints_competitors/page-2.4.html',
  'yandexmaps',
  'markerclusterer',
  'slider',
  'underscore'], function($, Marionette, GeoPointsTemplate, YandexMaps, MarkerClustererLib, SliderLib, _) {
  return Marionette.View.extend({
    template: _.template(GeoPointsTemplate),
    chel_geo: [55.1461447, 61.3838507],
    our_shops: null,
    marker_cluster: null,
    marker_cluster_our_shop: null,
    shopClusterer: null,
    networks_shops: null,
    info_window: null,
    mouse_start_x: 0,
    is_mouse_down: false,
    overlay_window: null,
    selected_marker: null,
    selected_radius_buttons_block: null,
    is_changed_radius: false,
    is_processing: false,
    is_calc_distance: false,
    calc_distance_coordinates: [],
    calc_distance_lines: null,
    calc_distance_circles: [],
    calc_distance_info_window: null,
    page_hash: '#geopoints_competitors',
    filters: {},
    selected_radius: null,
    selected_radius_styles: {
      strokeColor: '#0081db',
      strokeOpacity: 0.8,
      strokeWeight: 1,
      fillColor: '#0081db',
      fillOpacity: 0.17,
    },
    deletedMarkers: [],
    movedMarkers: [],
    editMarker: {
      geoObj: null,
      markerId: 0,
      initialPosition: null,
    },
    sw_lat: 0,
    sw_lng: 0,
    ne_lat: 0,
    ne_lng: 0,
    center_lat: 0,
    center_lng: 0,
    shopFoundByAddress: false,
    searchString: '',
    ui: {
      filter_region: '.gp-filter-region',
      filter_city: '.gp-filter-city',
      filter_shop: '.gp-filter-shop',
      filter_address: '.gp-filter-address',
      filter_network: '.gp-filter-network',
      filter_radius: '.gp-filter-filter-radius',
      filter_show_our_shop: '.gp-filter-chk-our-shop',
      filter_show_networks: '.gp-filter-chk-shops',
      filter_show_problems: '.gp-filter-chk-problems',
      filters_block: '.gp-filters',
      button_clean: '.gp-filter-clean',
      button_show: '.gp-filter-show',
      button_show_filters: '.gp-show-filter-button',
    },

    regions: {
      regions_area: '.gp-filter-list-wrap.region',
      cities_area: '.gp-filter-list-wrap.city',
      shops_area: '.gp-filter-list-wrap.shop',
      networks_area: '.gp-filter-list-wrap.network',
      address_area: '.gp-filter-list-wrap.address',
    },

    events: {
      'click @ui.button_show_filters': 'onClickFiltersShow',
      'click .gp-filter-chk': 'onClickFilterSelector',
      'click @ui.filter_region': 'onClickFilterRegion',
      'click @ui.filter_city': 'onClickFilterCity',
      'click @ui.filter_shop': 'onClickFilterShop',
      'click @ui.filter_network': 'onClickFilterNetwork',
      'click @ui.filter_address': 'onClickFilterAddress',
      'click @ui.button_clean': 'onClickClean',
      'click @ui.button_show': 'onClickShow',
    },

    initialize: function () {
      this.on('window:resize', function () {
        this.onResizeWindow();
      });

      this.on('body:click', function () {
        $.each(this.getRegions(), function (index, region) {
          if (region.currentView && region.currentView.hideList)
            region.currentView.hideList();
        });
      });
    },

    uploadGeopoints: function (url, urlOblect) {
      const urlCopy = new URL(urlOblect.href);
      return new Promise((resolve, reject) => {
        $.fn.app_ajax({
          url: url + urlCopy.search,
          request_type: 'GET',
          success_func: function (data) {
            const dataValues = data;
            if (dataValues.values) {
              resolve(dataValues.values);
            } else {
              resolve([]);
            }
          },
          error_func: function (error) {
            console.error({ uploadGeopoints: error });
            reject(error);
          },
        });
      });
    },

    loadGeopointsByAddress: async function () {
      $('.gp-map').show();
      $('.modal-spinner').show();
      const t = this;
      const urlBase = `${window.api_url + 'geoshops/'}`;
      const url = new URL(urlBase);
      url.searchParams.append('limit', 10);
      url.searchParams.append('offset', 0);
      const shop_network_id = this.ui.filter_network.attr('data-id');
      if (shop_network_id && shop_network_id > 0) {
        url.searchParams.append('shop_network_id', shop_network_id);
      }
      if (t.searchString && t.searchString !== '') {
        url.searchParams.append('address', t.searchString);
      }
      const p = [];
      p.push(t.uploadGeopoints('geoshops/', url));
      const result = await Promise.all(p)
        .then((res) => {
          if (res.values && Array.isArray(res.values)) {
            return res.values;
          } else {
            return res;
          }
        })
        .catch(err => {
          console.error(err);
          return [];
        })
        .finally(()=> $('.modal-spinner').hide());
      return result;
    },


    loadGeopoints: function (offset, boundsOld) {
      const t = this;
      const limit = 100000;
      //55.76, 37.64
      const bounds = window.geo_boundaries;
      const urlBase = `${window.api_url + 'geoshops/'}`;
      const url = new URL(urlBase);
      url.searchParams.append('limit', limit);
      url.searchParams.append('offset', offset);
      url.searchParams.append('sw_lat', bounds.sw_lat);
      url.searchParams.append('sw_lng', bounds.sw_lng);
      url.searchParams.append('ne_lat', bounds.ne_lat);
      url.searchParams.append('ne_lng', bounds.ne_lng);
      if (t.searchString && t.searchString !== '') {
        url.searchParams.append('address', t.searchString);
      }
      const shop_network_id = t.ui.filter_network.attr('data-id');
      const region_id = t.ui.filter_region.attr('data-id');
      const city_id = +t.ui.filter_city.attr('data-id');
      const address_id = +t.ui.filter_address.attr('data-id');
      if (shop_network_id && shop_network_id > 0) {
        url.searchParams.append('shop_network_id', shop_network_id);
      }
      if (region_id && region_id > 0) {
        url.searchParams.append('region_id', region_id);
      }
      if (city_id && city_id > 0) {
        url.searchParams.append('city_id', city_id);
      }
      const p = [];
      if (boundsOld && boundsOld.sw_lat && boundsOld.sw_lng && boundsOld.ne_lat && boundsOld.ne_lng) {
        let sw_lat_next = 0;
        let sw_lng_next = 0;
        let ne_lat_next = 0;
        let ne_lng_next = 0;
        if (bounds.sw_lat < boundsOld.sw_lat) {
          url.searchParams.set("sw_lat", bounds.sw_lat);
          url.searchParams.set("sw_lng", bounds.sw_lng);
          url.searchParams.set("ne_lat", boundsOld.sw_lat);
          url.searchParams.set("ne_lng", bounds.ne_lng);
          sw_lat_next = bounds.sw_lat;
          //console.log('подгрузка по низу карты', url.search);
          p.push(t.uploadGeopoints('geoshops/', url));
        }
        if (bounds.ne_lat > boundsOld.ne_lat) {
          url.searchParams.set("sw_lat", boundsOld.ne_lat);
          url.searchParams.set("sw_lng", bounds.sw_lng);
          url.searchParams.set("ne_lat", bounds.ne_lat);
          url.searchParams.set("ne_lng", bounds.ne_lng);
          ne_lat_next = bounds.ne_lat;
          // подгрузка по верху карты
          //console.log('подгрузка по верху карты', url.search);
          p.push(t.uploadGeopoints('geoshops/', url));
        }
        if (bounds.sw_lng < boundsOld.sw_lng) {
          url.searchParams.set("sw_lat", bounds.sw_lat);
          url.searchParams.set("sw_lng", bounds.sw_lng);
          url.searchParams.set("ne_lat", bounds.ne_lat);
          url.searchParams.set("ne_lng", boundsOld.sw_lng);
          sw_lng_next = bounds.sw_lng;
          // подгрузка по западу карты
          //console.log('подгрузка по западу карты', url.search);
          p.push(t.uploadGeopoints('geoshops/', url));
        }
        if (bounds.ne_lng > boundsOld.ne_lng) {
          url.searchParams.set("sw_lat", bounds.sw_lat);
          url.searchParams.set("sw_lng", boundsOld.ne_lng);
          url.searchParams.set("ne_lat", bounds.ne_lat);
          url.searchParams.set("ne_lng", bounds.ne_lng);
          ne_lng_next = bounds.ne_lng;
          //console.log('подгрузка по востоку карты', url.search);
          // подгрузка по востоку карты
          p.push(t.uploadGeopoints('geoshops/', url));
        }
        // bounds.sw_lat = Math.max(); v_sw_lat - lat_additive;
        // bounds.sw_lng = v_sw_lng - lng_additive;
        // bounds.ne_lat = v_ne_lat + lat_additive;
        // bounds.ne_lng = v_ne_lng + lng_additive;

      } else {
        p.push(t.uploadGeopoints('geoshops/', url));
      }
      const geoObjects = [];
      const setIds = new Set();

      Promise.all(p).then((results) => {
        results.map((res) => {
          res.map(v => {
            if (v.id && v.city_id && !setIds.has(v.id)) {
              if (city_id && v.city_id == city_id) {
                if (v.city_name && v.address) {
                  const name = `${v.city_name}, ${v.address}`;
                  geoObjects.push({ name, ...v });
                } else {
                  geoObjects.push(v);
                }
                setIds.add(v.id);
              } else if (!city_id) {
                if (v.city_name && v.address) {
                  const name = `${v.city_name}, ${v.address}`;
                  geoObjects.push({ name, ...v });
                } else {
                  geoObjects.push(v);
                }
                setIds.add(v.id);
              }
            }
          });
        });
        window.geopoints = geoObjects;
        t.shopFoundByAddress = false;

        let filteredShops = [];
        let filteredShopsByCity = [];

        if (address_id && address_id > 0) {
          url.searchParams.append('shop_network_id', shop_network_id);
          filteredShops = window.geopoints.filter(v => v.id === address_id);
          if (filteredShops.length > 0) {
            filteredShopsByCity = window.geopoints.filter(
              (v) => v.city_id === filteredShops[0].city_id
            );
            filteredShopsByCity.map((v) => {
              v.name = v.city_name + ', ' + v.address;
            });
          }
          filteredShops = [{id: 0, name: 'Все', lat: 0, lng: 0},  ...filteredShopsByCity ];
          t.getRegion('address_area').currentView?.resetCollection(t.networks_shops);

        } else {
          filteredShops = [{id: 0, name: 'Все', lat: 0, lng: 0},  ...window.geopoints ];
          t.getRegion('address_area').currentView?.resetCollection(filteredShops);
        }
        t.networks_shops = filteredShops; //window.geopoints
        t.DrawMapObjects();
        $('.modal-spinner').hide();
      });
    },

    onRender: function () {
      $('.gp-map').show();
      $('.modal-spinner').show();
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
          t.loadGeopoints(0, boundsOld);
          t.DrawMapObjects();
        }
      };

      this.onResizeWindow();
      if (!window.gp_map) {
        ymaps.ready(function () {
          window.gp_map = new ymaps.Map($('.gp-map')[0], {
            center: [55.76, 37.64],
            zoom: 13,
            controls: [],
          }, {
            yandexMapDisablePoiInteractivity: true
          });
          window.gp_map.events.add('boundschange', ymapsBoundsChange);
          const ruler = new ymaps.control.RulerControl();
          window.gp_map.controls.add(ruler, {
            scaleLine: false,
          });
          t.showRegions();
          t.showAllCities();
          t.getAllShops()
            .then(() => {
              t.showAllShops();
            })
          t.showNetworks();
        });
      } else {
        setTimeout(function () {
          t.showRegions();
          t.showAllCities();
          t.getAllShops()
            .then(() => {
              t.showAllShops();
            })
          t.showNetworks();
          t.getGeopoints();
        }, 0);
      }

      $('body')
        .mousemove(function (e) {
          t.change_point_radius(e);
        })
        .mouseup(function () {
          t.is_mouse_down = false;
        });

      // Try W3C Geolocation (Preferred)
      if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
          function (position) {
            ymaps.ready(function () {
              window.gp_map.setCenter([
                position.coords.latitude,
                position.coords.longitude,
              ]);
            });
          },
          function () {
            ymaps.ready(function () {
              window.gp_map.setCenter([
                t.chel_geo[0],
                t.chel_geo[1],
              ]);
            });
          }
        );
      }
      // Browser doesn't support Geolocation
      else {
        ymaps.ready(function () {
          window.gp_map.setCenter([t.chel_geo[0], t.chel_geo[1]]);
        });
      }

      // init slider
      this.ui.filter_radius.slider({ tooltip: 'hide' });
      this.ui.filter_radius
        .on('change', function () {
          var value = this.value;
          var min = +value.split(',')[0],
            max = +value.split(',')[1];

          var min_str =
              min < 1000
                ? min + 'м'
                : Math.round(min / 100.0) / 10 + 'км',
            max_str =
              max < 1000
                ? max + 'м'
                : max >= 15000
                  ? '>15км'
                  : Math.round(max / 100.0) / 10 + 'км';

          t.$el
            .find('.gp-filter-filter-radius-value')
            .text(min_str + ' - ' + max_str);
        })
        .change();

      this.restoreFilters();
    },

    onDestroy: function () {
      $('body').unbind('mousemove').unbind('mouseup');
      if (
        this.marker_cluster_our_shop &&
        this.marker_cluster_our_shop.removeAll
      ) {
        this.marker_cluster_our_shop.removeAll();
      }

      if (this.marker_cluster)
        for (var i = 0; i < this.marker_cluster.length; i++)
          if (
            this.marker_cluster[i] &&
            this.marker_cluster[i].removeAll
          ) {
            this.marker_cluster[i].removeAll();
          }
      $('.gp-map').hide();

      window.gp_map.events.remove('boundschange');
      window.gp_map.events.remove('actionend');

      if (this.selected_radius) {
        window.gp_map.geoObjects.remove(this.selected_radius);
      }

      if (this.selected_radius_buttons_block) {
        window.gp_map.geoObjects.remove(
          this.selected_radius_buttons_block
        );
      }
    },

    onClickFiltersShow: function () {
      this.ui.filters_block.toggle();
    },

    onResizeWindow: function () {
      if ($.fn.mobile_check()) {
        $('.gp-map').css({
          height:
            $(window).height() -
            $('.navigation-bar').height() +
            'px',
          width: $(window).width() + 'px',
          top: $('.navigation-bar').height() + 'px',
        });
      } else {
        $('.gp-map').css({
          height: $(window).height() + 'px',
          width: $(window).width() - $('.menu').width() + 'px',
          left: $('.menu').width() + 'px',
        });
      }

      this.ui.filters_block.css('max-height', $(window).height() + 'px');
    },

    getAllShops: function () {
      return new Promise((res, rej) => {
        let Radio = require('backbone.radio');
        let channel = Radio.channel('shops');
        channel.replyOnce('all_shops:answer', function () {
          res();
        });

        channel.request('all_shops:get', true);
      })
    },

    change_point_radius: function (e) {
      if (!this.is_mouse_down) return;
      e.preventDefault();
      e.stopPropagation();
      var current_x = e.clientX;
      if (current_x < this.mouse_start_x) return;

      var wh = (current_x - this.mouse_start_x) * 2;
      $('.gp-radius').css({
        width: wh + 'px',
        height: wh + 'px',
        margin:
          '-' +
          parseInt(wh / 2) +
          'px 0 0 -' +
          parseInt(wh / 2) +
          'px',
      });
      this.is_changed_radius = true;
      this.selected_marker.data.new_radius = getRadiusInMetres(
        $('.gp-radius').width() / 2,
        window.gp_map
      );
    },

    showRegions: function () {
      var t = this;
      var Radio = require('backbone.radio');
      var channel = Radio.channel('shops');
      channel.replyOnce('regions:answer', function (regions) {
        requirejs(['views/statistic/filter'], function (FilterView) {
          var view = new FilterView({
            collection: regions,
            parent_elem: t.ui.filter_region,
          });
          view.on('select', function (view) {
            t.ui.filter_city.attr('data-id', 0).text('Все');
            t.ui.filter_shop.attr('data-id', 0).text('Все');
            if (t.getRegion('cities_area').currentView) {
              t.getRegion(
                'cities_area'
              ).currentView.clearFilterText();
              t.getRegion('cities_area').currentView.filter_data(
                'region_id',
                view.model.get('id')
              );
            }
            if (t.getRegion('shops_area').currentView) {
              t.getRegion(
                'shops_area'
              ).currentView.clearFilterText();
              t.getRegion('shops_area').currentView.filter_data(
                'region_id',
                view.model.get('id')
              );
            }

            t.ui.filter_address.attr('data-id', 0).text('Все');
            t.showGeoPoints();
            if (t.getRegion('address_area').currentView)
              t.getRegion(
                'address_area'
              ).currentView.clearFilterText();
          });
          t.showChildView('regions_area', view);
          if (t.filters.region_id) {
            view.select(+t.filters.region_id);
            delete t.filters.region_id;
          }
        });
      });
      channel.request('regions:get');
    },

    showAllCities: function () {
      var t = this;
      var Radio = require('backbone.radio');
      var channel = Radio.channel('shops');
      channel.replyOnce('all_cities:answer', function (cities) {
        requirejs(['views/statistic/filter'], function (FilterView) {
          var view = new FilterView({
            collection: cities,
            parent_elem: t.ui.filter_city,
          });
          view.on('select', function (current_view) {
            const cityAttributes =
              current_view.options.model.attributes;
            if (
              window.gp_map &&
              cityAttributes.southwest_lat &&
              cityAttributes.southwest_lng &&
              cityAttributes.northeast_lat &&
              cityAttributes.northeast_lng
            ) {
              window.gp_map.setBounds([
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
      channel.request('all_cities:get');
    },

    showNetworks: function () {
      var t = this;
      var Radio = require('backbone.radio');
      var channel = Radio.channel('shops');
      channel.replyOnce('networks:answer', function (networks) {
        requirejs(['views/statistic/filter'], function (FilterView) {
          var view = new FilterView({
            collection: networks,
            parent_elem: $('.gp-filter-network'),
            full_search: true,
          });
          view.on('select', function (view) {
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
          t.showChildView('networks_area', view);
          if (t.filters.network_id) {
            view.select(+t.filters.network_id);
            delete t.filters.network_id;
          }
        });
      });
      channel.request('networks:get');
    },

    showGeoPoints: function () {
      var shop_network_id = +this.ui.filter_network.attr('data-id'),
        region_id = +this.ui.filter_region.attr('data-id'),
        city_id = +this.ui.filter_city.attr('data-id');

      if (!shop_network_id) return;

      var t = this;
      var Radio = require('backbone.radio');
      var channel = Radio.channel('shops');
      channel.replyOnce(
        'geopoints_address:answer',
        function (geo_points) {
          requirejs(
            ['views/statistic/filter'],
            function (FilterView) {
              var view = new FilterView({
                collection: geo_points,
                parent_elem: $('.gp-filter-address'),
                full_search: true,
                check_validation: false
              });

              const selectHandler = (modelData) => {
                if (modelData.model.id === 0) {
                  t.searchString = '';
                  t.ui.filter_address
                    .text('Все')
                    .attr('data-id', 0)
                }
                if ($.isEmptyObject(t.filters))
                {
                  t.saveFilters();
                  t.DrawMapObjects();
                }
                if (!modelData.model.get('id')) {
                  return;
                }
                t.saveFilters();
                t.DrawMapObjects();
                t.loadGeopoints(0)
                ymaps.ready(function () {
                  window.gp_map.setCenter([modelData.model.get('lat'), modelData.model.get('lng')]);
                  window.gp_map.setZoom(15);
                });
              }

              view.on('select', selectHandler);
              view.on('searchByText', async function(searchData) {
                if (searchData.addByText) {
                  t.searchString = searchData.name;
                  const points = await t.loadGeopointsByAddress();
                  t.searchString = '';
                  if (Array.isArray(points) && points.length > 0) {
                    let selectedPoint = points[0];
                    if (Array.isArray(selectedPoint)) {
                      selectedPoint = selectedPoint[0];
                    }
                    if (selectedPoint && selectedPoint.lat && selectedPoint.lng) {
                      await ymaps.ready(function () {
                        window.gp_map.setCenter([selectedPoint.lat, selectedPoint.lng]);
                        window.gp_map.setZoom(15);
                      });
                      t.ui.filter_city.attr('data-id', selectedPoint.city_id ?? 0).text(selectedPoint.city_name ?? 'Все');
                      t.ui.filter_address.attr('data-id', selectedPoint.id ?? 0).text(selectedPoint.city_name + ', ' +  selectedPoint.address);
                      await t.loadGeopoints(0);
                    }
                  }
                }
              });

              t.showChildView('address_area', view);
              if (t.filters.geopoint_id) {
                view.select(+t.filters.geopoint_id);
                delete t.filters.geopoint_id;
              }
            }
          );
        }
      );
      channel.request('geopoints_address:get', {shop_network_id: shop_network_id, region_id: region_id, city_id: city_id});
    },

    getGeopoints: function () {
      var t = this;
      var Radio = require('backbone.radio');
      var channel = Radio.channel('shops');
      channel.replyOnce('geopoints:answer', function (geopoints) {
        t.networks_shops = geopoints;
        if ($.isEmptyObject(t.filters)) t.DrawMapObjects();
      });
      channel.request('geopoints:get');
    },

    showAllShops: function () {
      var t = this;
      var Radio = require('backbone.radio');
      var channel = Radio.channel('shops');
      channel.replyOnce('all_shops_filter:answer', function (shops) {
        t.our_shops = shops;

        if ($.isEmptyObject(t.filters)) {
          t.DrawMapObjects();
        }

        requirejs(['views/statistic/filter'], function (FilterView) {
          for (var i = 0; i < shops.length; i++) {
            shops[i].name =
              shops[i].address +
              (shops[i].id ? ' (' + shops[i].id + ')' : '');
          }

          var view = new FilterView({
            collection: shops,
            parent_elem: t.ui.filter_shop,
            full_search: true,
            enter_search: true,
          });
          view.on('select', function (view1) {
            if (t.getRegion('shops_area').currentView)
              t.getRegion(
                'shops_area'
              ).currentView.clearFilterText();

            if (t.getRegion('cities_area').currentView) {
              t.getRegion(
                'cities_area'
              ).currentView.clearFilterText();
            }

            if (t.getRegion('regions_area').currentView) {
              t.getRegion(
                'regions_area'
              ).currentView.clearFilterText();
            }
          });

          t.showChildView('shops_area', view);

          if (t.filters.shop_id) {
            view.select(+t.filters.shop_id);
            delete t.filters.shop_id;
          }
        });
      });
      channel.request('all_shops_filter:get');
    },

    onClickFilterSelector: function (e) {
      var elem = $(e.currentTarget);
      if (elem.hasClass('on')) elem.addClass('off').removeClass('on');
      else elem.addClass('on').removeClass('off');
    },

    onClickFilterRegion: function (e) {
      if ($(e.currentTarget).hasClass('disable')) return;
      e.stopPropagation();
      var is_visible =
        this.getRegion('regions_area').currentView.isShowingList();
      $.each(this.getRegions(), function (index, region) {
        if (region.currentView && region.currentView.hideList)
          region.currentView.hideList();
      });

      if (!is_visible)
        this.getRegion('regions_area').currentView.showList();
    },

    onClickFilterCity: function (e) {
      if ($(e.currentTarget).hasClass('disable')) return;
      e.stopPropagation();
      var is_visible =
        this.getRegion('cities_area').currentView.isShowingList();
      $.each(this.getRegions(), function (index, region) {
        if (region.currentView && region.currentView.hideList)
          region.currentView.hideList();
      });

      if (!is_visible)
        this.getRegion('cities_area').currentView.showList();
    },

    onClickFilterShop: function (e) {
      if ($(e.currentTarget).hasClass('disable')) return;
      e.stopPropagation();
      var is_visible =
        this.getRegion('shops_area').currentView.isShowingList();
      $.each(this.getRegions(), function (index, region) {
        if (region.currentView && region.currentView.hideList)
          region.currentView.hideList();
      });

      if (!is_visible)
        this.getRegion('shops_area').currentView.showList();
    },

    onClickFilterNetwork: function (e) {
      if ($(e.currentTarget).hasClass('disable')) return;
      e.stopPropagation();
      var is_visible =
        this.getRegion('networks_area').currentView.isShowingList();
      $.each(this.getRegions(), function (index, region) {
        if (region.currentView && region.currentView.hideList)
          region.currentView.hideList();
      });

      if (!is_visible)
        this.getRegion('networks_area').currentView.showList();
    },

    onClickFilterAddress: function (e) {
      const t = this;
      if ($(e.currentTarget).hasClass('disable')) return;
      e.stopPropagation();
      var is_visible =
        this.getRegion('address_area').currentView.isShowingList();
      $.each(this.getRegions(), function (index, region) {
        if (region.currentView && region.currentView.hideList)
          region.currentView.hideList();
      });

      if (!is_visible)
        this.getRegion('address_area').currentView.showList();
      const current_view = this.getRegion('address_area').currentView;
      console.log({current_view})
      current_view.on('searchByText', async function(searchData) {
        t.searchString = searchData.name;
        const points = await t.loadGeopointsByAddress();
        t.searchString = '';
        if (Array.isArray(points) && points.length > 0) {
          let selectedPoint = points[0];
          if (Array.isArray(selectedPoint)) {
            selectedPoint = selectedPoint[0];
          }
          if (selectedPoint && selectedPoint.lat && selectedPoint.lng) {
            await ymaps.ready(function () {
              window.gp_map.setCenter([selectedPoint.lat, selectedPoint.lng]);
              window.gp_map.setZoom(15);
            });
            t.ui.filter_city.attr('data-id', selectedPoint.city_id ?? 0).text(selectedPoint.city_name ?? 'Все');
            t.ui.filter_address.attr('data-id', selectedPoint.id ?? 0).text(selectedPoint.city_name + ', ' +  selectedPoint.address);
            await t.loadGeopoints(0);
          }
        }
      });



    },

    onClickClean: function () {
      this.ui.filter_region.text('Все').attr('data-id', 0);
      this.ui.filter_network.text('Все').attr('data-id', 0);

      this.ui.filter_city.text('Все').attr('data-id', 0);
      this.ui.filter_shop.text('Все').attr('data-id', 0);
      this.ui.filter_address
        .text('Все')
        .attr('data-id', 0)
        .addClass('disable');

      $.each(this.getRegions(), function (index, region) {
        if (region.currentView && region.currentView.clearFilterText)
          region.currentView.clearFilterText();
      });

      this.getRegion('cities_area').currentView.filter_data();
      this.getRegion('shops_area').currentView.filter_data();
    },

    DrawMapObjects: function () {
      if (!this.our_shops || !this.networks_shops) return;

      var is_show_our_shops = this.ui.filter_show_our_shop.hasClass('on'),
        is_show_networks_shops =
          this.ui.filter_show_networks.hasClass('on'),
        is_show_problems = this.ui.filter_show_problems.hasClass('on'),
        region_id = +this.ui.filter_region.attr('data-id'),
        city_id = +this.ui.filter_city.attr('data-id'),
        shop_id = +this.ui.filter_shop.attr('data-id'),
        network_id = +this.ui.filter_network.attr('data-id'),
        geopoint_id = +this.ui.filter_address.attr('data-id');

      var markers = this.GetDrawOurShops(
        is_show_our_shops,
        region_id,
        city_id,
        shop_id
      );
      this.DrawOurShopsMarkers(markers);

      markers = this.GetDrawNetworkhops(
        is_show_networks_shops,
        region_id,
        city_id,
        shop_id,
        network_id,
        geopoint_id,
        is_show_problems
      );
      this.DrawShopsMarkers(markers);

      $('.modal-spinner').hide();
    },

    check_filter_conditions: function (
      our_shop,
      region_id,
      city_id,
      shop_id
    ) {
      if (
        (region_id == 0 || region_id == our_shop.region_id) &&
        (city_id == 0 || city_id == our_shop.city_id) &&
        (shop_id == 0 || shop_id == our_shop.id)
      )
        return true;
      return false;
    },

    GetDrawOurShops: function (
      is_show_our_shops,
      region_id,
      city_id,
      shop_id
    ) {
      if (!is_show_our_shops) return [];

      var t = this,
        markers = [];
      for (var i = 0; i < t.our_shops.length; i++) {
        if (t.our_shops[i].id === 0) {
          continue;
        } else if (
          this.check_filter_conditions(
            t.our_shops[i],
            region_id,
            city_id,
            shop_id
          )
        ) {
          IconContentLayout = ymaps.templateLayoutFactory.createClass(
            '<div class="gp-info-shop">' +
            '<div class="gp-info-shop-info">' +
            '<p class="gp-info-shop-name">Наш магазин</p>' +
            '<p class="gp-info-shop-address">' +
            t.our_shops[i].address +
            '</p>' +
            '</div>' +
            '<div class="clearFix"></div>' +
            '</div>'
          );
          let placeMark = new ymaps.Placemark(
            [t.our_shops[i].lat, t.our_shops[i].lng],
            {
              balloonContent: {
                id: t.our_shops[i].id,
                address: t.our_shops[i].address,
              },
            },
            {
              iconLayout: 'default#imageWithContent',
              iconImageHref:
                'imgs/markers/our-shop.png#' +
                t.our_shops[i].id,
              iconImageSize: [25, 33],
              iconImageOffset: [-5, -38],
              iconContentLayout: IconContentLayout,
            }
          );

          markers.push(placeMark);
        }
      }

      return markers;
    },

    is_marker_not_in_cluster: function (marker) {
      for (
        var i = 0,
          clusters = this.marker_cluster_our_shop.getClusters();
        i < clusters.length;
        i++
      ) {
        for (
          var j = 0, markers = clusters[i].getMarkers();
          j < markers.length;
          j++
        ) {
          if (markers[j].data.id == marker.data.id)
            return markers.length == 1;
        }
      }

      return true;
    },

    find_marker_in_cluster: function (id) {
      for (
        var i = 0, markers = this.marker_cluster_our_shop.markers_;
        i < markers.length;
        i++
      )
        if (markers[i].data.id == id) return markers[i];
      return null;
    },

    DrawOurShopsMarkers: function (markers) {
      if (markers.length >= 0) {
        window.gp_map.geoObjects.removeAll();
      }

      ymaps.ready(function () {
        IconContentLayout = ymaps.templateLayoutFactory.createClass(
          '<div style="color: #FFFFFF; font-weight: bold;">{{ properties.geoObjects.length }}</div>'
        );
        clusterer = new ymaps.Clusterer({
          clusterIcons: [
            {
              href: 'imgs/markers/our-shop.png',
              size: [25, 33],
              offset: [-25, -30],
            },
          ],
          clusterNumbers: [30],
          clusterIconContentLayout: IconContentLayout,
        });
        getPointData = function (index, content) {
          return {
            balloonContentBody:
              '<div class="gp-info-shop">' +
              '<div class="gp-info-shop-info">' +
              '<p class="gp-info-shop-name">Наш магазин</p>' +
              '<p class="gp-info-shop-address">' +
              content.address +
              '</p>' +
              '</div>' +
              '<div class="clearFix"></div>' +
              '</div>',
          };
        };
        points = [];
        for (let i = 0; i < markers.length; i++) {
          points.push([
            markers[i].geometry._coordinates[0],
            markers[i].geometry._coordinates[1],
          ]);
        }
        geoObjects = [];
        for (let i = 0; i < points.length; i++) {
          geoObjects[i] = new ymaps.Placemark(
            points[i],
            getPointData(
              i,
              markers[i].properties._data.balloonContent
            ),
            {
              iconLayout: 'default#imageWithContent',
              iconImageHref: 'imgs/markers/our-shop.png',
              iconImageSize: [25, 33],
              iconImageOffset: [-10, -30],
              hideIconOnBalloonOpen: false,
              balloonOffset: [0, -30],
            }
          );
        }
        clusterer.add(geoObjects);
        window.gp_map.geoObjects.add(clusterer);
      });
    },

    GetDrawNetworkhops: function (
      is_show_networks_shops,
      region_id,
      city_id,
      shop_id,
      network_id,
      geopoint_id,
      is_show_problems
    ) {
      var markers = [];
      if (!is_show_networks_shops) return markers;

      var t = this;
      var slider_value = this.ui.filter_radius.val();
      var min = +slider_value.split(',')[0],
        max = +slider_value.split(',')[1];
      max = max == 15000 ? -1 : max;
      const oneNetworkShop = t.networks_shops.length === 1;
      const filteredShops = t.networks_shops
        .filter(item => oneNetworkShop || geopoint_id == 0 || (item && item.id && geopoint_id == item.id)) //// by geopoint
        .filter(item => !t.deletedMarkers.includes(item.id))
        .filter(item => oneNetworkShop || region_id == 0 || region_id == item.region_id) // by region
        .filter(item => oneNetworkShop || city_id == 0 || city_id == item.city_id)       // by city
        .filter(item => oneNetworkShop || network_id == 0 || network_id == item.shop_network_id) // by network
        .filter(item => oneNetworkShop || shop_id == 0 || t.check_shop_id(item.shops, shop_id))
        .filter(item => min <= item.radius &&
          (max == -1 || max >= item.radius) &&
          (!is_show_problems ||
            this.check_nearest_network_shop(
              item.lat,
              item.lng,
              item.shop_network_id,
              300,
              item.id
            )
          )
        )
      filteredShops.map(item =>
        {
          const movedMarkersGoords = t.movedMarkers.find(v => v.id === item.id)?.coords ?? [item.lat, item.lng];
          let marker = new ymaps.Placemark(
            movedMarkersGoords,
            {
              balloonContent: {
                id: item.id,
                radius: item.radius,
                new_radius: item.radius,
                lat: item.lat,
                lng: item.lng,
                networks_shop: item,
              },
            },
            {
              iconLayout: 'default#imageWithContent',
              iconImageHref: item.icon
                ? item.icon +
                '#' +
                item.id
                : 'imgs/markers/default.png#' +
                item.id,
            }
          );

          // find network in markers
          var indx = -1;
          for (var j = 0; j < markers.length; j++) {
            if (
              markers[j].shop_network_id ==
              item.shop_network_id
            ) {
              indx = j;
              break;
            }
          }

          if (indx == -1) {
            markers.push({
              shop_network_id:
              item.shop_network_id,
              name:item.name,
              icon: item.icon
                ? item.icon
                : 'imgs/markers/default.png',
              markers: [],
            });
            indx = markers.length - 1;
          }

          markers[indx].markers.push(marker);
        }

      )
      if (window.addressSearcString && window.addressSearcString !== '') {
        window.addressSearcString === ''; // принудительный сброс
      }
      return markers;
    },

    check_nearest_network_shop: function (
      lat,
      lng,
      shop_network_id,
      distance,
      current_geopoint_id
    ) {
      for (var i = 0; i < this.networks_shops.length; i++) {
        if (
          this.networks_shops[i].shop_network_id != shop_network_id ||
          this.networks_shops[i].id == current_geopoint_id
        )
          continue;

        if (
          this.calc_distance(
            lat,
            lng,
            this.networks_shops[i].lat,
            this.networks_shops[i].lng
          ) <= distance
        )
          return true;
      }

      return false;
    },

    find_nearest_marker: function (id, shop_network_id, lat, lng) {
      const t = this;
      const geoShops = t.shopClusterer.getGeoObjects();

      const filteredGeoShops = geoShops.filter(
        (shop) =>
          shop.properties.get('shop_network_id', 0) ===
          shop_network_id &&
          shop.properties.get('markerId', 0) !== id
      );
      for (const shop of filteredGeoShops) {
        const coords = shop.geometry.getCoordinates();
        const _lat = coords[0];
        const _lng = coords[1];
        const dist = Math.sqrt(
          Math.pow(_lat - lat, 2) + Math.pow(_lng - lng, 2)
        );
        const tempX = 0.0003;

        if (dist < tempX) {
          const _id = shop.properties.get('markerId', 0);
          return _id;
        }
      }
      return 0;
    },

    findIndexInArray: function (array, key, id) {
      for (var i = 0; i < array.length; i++) {
        if (array[i][key] == id) {
          return i;
        }
      }
      return -1;
    },
    DrawShopsMarkers: async function (markers) {
      let t = this;
      if (markers.length >= 0) {
        window.gp_map.geoObjects.removeAll();
      }
      this.marker_cluster = markers;

      async function resetPlacemark() {
        if (t.editMarker.geoObj && t.editMarker.markerId) {
          await RadiusEditCancel({ data: { ...t.editMarker } });
        }
      }
      const ShowClick = async (event) => {
        resetPlacemark();

        const markerId =
          event.originalEvent.target.properties._data.markerId;
        await $.fn.app_ajax({
          url: 'geoshops/?id=' + markerId,
          request_type: 'GET',
          bad_request_func: (response) => {
            console.error('Ошибка запроса ', {
              markerId,
              response,
            });
          },
          error_func: (response) => {
            console.error('Ошибка запроса ', {
              markerId,
              response,
            });
          },
          success_func: (response) => {
            let data = response.values[0];
            ShowGeoBalloon(event.originalEvent.target, data);
          },
        });
      };

      const ShowGeoBalloon = (geoObj, data) => {
        geoObj.properties._data.balloonContentLayout =
          ymaps.templateLayoutFactory.createClass(
            '<div class="gp-info-shop">' +
            '<img class="gp-info-shop-img" src="' +
            (data.shop_network_logo
              ? data.shop_network_logo
              : '') +
            '">' +
            '<div class="gp-info-shop-info">' +
            '<p class="gp-info-shop-name">' +
            data.shop_network_name +
            '</p>' +
            '</div>' +
            '<div class="clearFix"></div>' +
            '<p class="gp-info-shop-address">' +
            data.city_name +
            ', ' +
            data.address +
            '</p>' +
            '<p class="gp-info-shop-creator">Создатель метки:</p>' +
            '<p class="gp-info-shop-user">' +
            data.created_by.name +
            '</p>' +
            '<p class="gp-info-shop-creator">Дата создания:</p>' +
            '<p class="gp-info-shop-user">' +
            data.created +
            '</p>' +
            ($.inArray(
              'geopoint_edit_deny',
              window.user.permissions
            ) === -1
              ? '<p class="gp-info-shop-edit">Редактировать метку</p>' +
              '<p class="gp-info-shop-delete">Удалить метку</p>'
              : '') +
            '</div>',
            {
              build: function () {
                geoObj.properties._data.balloonContentLayout.superclass.build.call(
                  this
                );
                $('.gp-info-shop-delete').bind(
                  'click',
                  { geoObj },
                  DeleteGeopoint
                );
                $('.gp-info-shop-edit').bind(
                  'click',
                  { geoObj },
                  EditGeopoint
                );
              },
              clear: function () {
                $('.gp-info-shop-delete').unbind(
                  'click',
                  DeleteGeopoint
                );
                $('.gp-info-shop-edit').unbind(
                  'click',
                  EditGeopoint
                );
                geoObj.properties._data.balloonContentLayout.superclass.clear.call(
                  this
                );
              },
            }
          );
        geoObj.options.set(
          'balloonContentLayout',
          geoObj.properties._data.balloonContentLayout
        );
        geoObj.properties._data.balloonContentBody =
          '<div class="gp-info-shop">' +
          '<div class="gp-info-loader"></div>' +
          '</div>';
        geoObj.options.set(
          'balloonContentBody',
          geoObj.properties._data.balloonContentBody
        );
      };

      const DeleteGeopoint = (event) => {
        requirejs(
          ['views/schedule_graphic/confirm_delete-2.1'],
          function (ConfirmDeleteView) {
            const markerId =
              event.data.geoObj.properties._data.markerId;
            var view = new ConfirmDeleteView({
              geo_point_id: markerId,
            });
            view.on('deleted', function () {
              t.shopClusterer.remove(event.data.geoObj);
              t.deletedMarkers.push(event.data.geoObj.properties._data.markerId);
            });
            window.app.getView().trigger('modal:show', {
              region: view.region_name,
              view: view,
            });
          }
        );
      };
      const EditGeopoint = (event) => {
        const geoObj = event.data.geoObj;
        const markerId = geoObj.properties.get('markerId', 0);
        const markerIcon = geoObj.properties.get('markerIcon', '');
        const radius = geoObj.properties.get('radius', 0);
        const shop_network_id = geoObj.properties.get(
          'shop_network_id',
          0
        );
        const employee_id = geoObj.properties.get('employee_id', 0);
        const city_id = geoObj.properties.get('city_id', 0);
        const region_id = geoObj.properties.get('region_id', 0);

        const initialPosition = geoObj.geometry.getCoordinates();
        const initialBounds = window.gp_map.getBounds();
        const _lat = initialPosition[0];
        const _lng = initialPosition[1];
        $('.gp-radius-ok').show();
        $('.gp-radius-link').hide();
        $(document).find('.ymaps-2-1-79-balloon__close').hide();
        geoObj.balloon.close();

        if (t.selected_radius) {
          t.selected_radius.setParent(null);
        }

        if (t.selected_radius_buttons_block) {
          t.selected_radius.editor.stopEditing();
        }
        t.selected_radius = new ymaps.Circle(
          [[_lat, _lng], radius],
          {
            markerId,
            markerIcon,
            radius,
            shop_network_id,
            employee_id,
            city_id,
            region_id,
          },
          {
            strokeWidth: t.selected_radius_styles.strokeWeight,
            strokeColor: t.selected_radius_styles.strokeColor,
            fillColor: t.selected_radius_styles.fillColor,
            fillOpacity: t.selected_radius_styles.fillOpacity,
            draggable: true,
            editable: true,
          }
        );

        t.selected_radius.geometry.setCoordinates(
          geoObj.geometry.getCoordinates()
        );
        t.selected_radius.geometry.events.add('change', function () {
          geoObj.options.set('draggable', true);
          geoObj.geometry.setCoordinates(
            t.selected_radius.geometry.getCoordinates()
          );
          geoObj.options.set('draggable', false);
        });

        window.gp_map.geoObjects.add(t.selected_radius);

        const radiusControlButtons =
          document.querySelector('.gp-edit-buttons');
        const lat1 = t.selected_radius.geometry.getCoordinates()[0];
        const lon1 = t.selected_radius.geometry.getBounds()[1][1];

        const balloonRootClass = 'custom-balloon-root';
        const BalloonContentLayout =
          ymaps.templateLayoutFactory.createClass(
            '<div class=' +
            balloonRootClass +
            '>' +
            '<div class="arrow custom-balloon-pin"></div>' +
            '<div class="custom-balloon-body custom-balloon">$[properties.balloonContent]</div>' +
            '</div>'
          );

        t.selected_radius_buttons_block = new ymaps.Placemark(
          [lat1, lon1],
          { balloonContent: radiusControlButtons.innerHTML },
          {
            balloonLayout: BalloonContentLayout,
          }
        );

        window.gp_map.geoObjects.add(t.selected_radius_buttons_block);
        t.selected_radius_buttons_block.balloon.open(
          '',
          {},
          { closeButton: false }
        );

        t.selected_radius.events.add(
          'geometrychange',
          async function () {
            t.selected_radius_buttons_block.geometry.setCoordinates(
              [
                t.selected_radius.geometry.getCoordinates()[0],
                t.selected_radius.geometry.getBounds()[1][1],
              ]
            );
          }
        );

        t.selected_radius.editor.startEditing();
        let linkID;

        const dragendRadius = async (event) => {
          const newCenter =
            await t.selected_radius.geometry.getCoordinates();
          const geoMarker = t.shopClusterer
            .getGeoObjects()
            .find((v) => v.properties.get('markerId') === markerId);
          if (t.shopClusterer && geoMarker) {
            t.shopClusterer.remove(geoMarker);
            geoMarker.geometry.setCoordinates(newCenter);
            t.shopClusterer.add(geoMarker);
          }

          let link = t.find_nearest_marker(
            markerId,
            shop_network_id,
            newCenter[0],
            newCenter[1]
          );
          if (link) {
            $('.gp-radius-ok').hide();
            $('.gp-radius-link').show();
            $(`.${balloonRootClass}`).css('min-width', 160);
            $('.gp-radius-ok').hide();
            $('.gp-radius-link').show();
          } else {
            $('.gp-radius-ok').show();
            $('.gp-radius-link').hide();
            $(`.${balloonRootClass}`).css('min-width', 100);
            $('.gp-radius-ok').show();
            $('.gp-radius-link').hide();
          }
          linkID = link;
          $(document).one(
            'click',
            '.gp-radius-link',
            { geoObj, markerId, linkID },
            MergeGeoPoints
          );
        };
        t.selected_radius.editor.events.add('dragend', dragendRadius);

        $(document).one(
          'click',
          '.gp-radius-cancel',
          { geoObj, initialPosition, initialBounds, markerId },
          RadiusEditCancel
        );
        $(document).one(
          'click',
          '.gp-radius-ok',
          { geoObj, markerId },
          UpdateGeopoint
        );

        t.editMarker.geoObj = geoObj;
        t.editMarker.initialPosition = initialPosition;
        t.editMarker.markerId = markerId;
      };

      const RadiusEditCancel = async (event) => {
        const geoObj = await event.data.geoObj;
        const markerId = await event.data.markerId;
        const initialPosition =
          event.data.initialPosition ?? t.editMarker.initialPosition;
        const initialBounds = event.data.initialBounds;
        if (geoObj) {
          if (t.shopClusterer) {
            const geoMarker = t.shopClusterer
              .getGeoObjects()
              .find(
                (v) => v.properties.get('markerId') === markerId
              );
            if (geoMarker) {
              t.shopClusterer.remove(geoMarker);
              t.selected_radius_buttons_block.balloon.close();
              window.gp_map.geoObjects.remove(
                t.selected_radius_buttons_block
              );
              t.selected_radius.setParent(null);
              t.selected_radius.editor.stopEditing();

              geoMarker.geometry.setCoordinates(initialPosition);
              geoMarker.events.add('click', ShowClick);
              t.shopClusterer.add(geoMarker);
              if (initialBounds) {
                window.gp_map.setBounds(
                  event.data.initialBounds
                );
              }
              t.editMarker.initialPosition = null;
              t.editMarker.markerId = 0;
              t.editMarker.geoObj = null;
            }
          }
        }
        $(document).off('click', '.gp-radius-ok');
      };

      const MergeGeoPoints = async (event) => {
        const geoObj = await event.data.geoObj;
        const markerIdSave = await event.data.markerId;
        const linkID = await event.data.linkID;
        if (t.is_processing) {
          return;
        }
        t.is_processing = true;
        $.fn.app_ajax({
          url: 'merge_geo_points/',
          request_type: 'POST',
          content_type: 'application/json',
          data_type: 'json',
          data: {
            object: {
              id: markerIdSave,
              link_to: linkID,
            },
          },
          success_func: function () {
            t.is_processing = false;
            t.selected_radius_buttons_block.balloon.close();
            window.gp_map.geoObjects.remove(
              t.selected_radius_buttons_block
            );
            t.selected_radius.setParent(null);
            t.selected_radius.editor.stopEditing();
            t.shopClusterer.remove(geoObj);
          },
          error_func: function () {
            t.is_processing = false;
          },
          bad_request_func: function () {
            t.is_processing = false;
          },
        });
      };

      const UpdateGeopoint = async (event) => {
        const geoObj = await event.data.geoObj;
        const markerIdSave = await event.data.markerId;
        if (t.is_processing) {
          return;
        }
        const req_data = {
          object: {
            id: markerIdSave,
            lat: await geoObj.geometry.getCoordinates()[0],
            lng: await geoObj.geometry.getCoordinates()[1],
            radius: t.selected_radius.geometry._radius,
          },
        };
        t.is_processing = true;
        $.fn.app_ajax({
          url: 'update_geoshop/',
          request_type: 'POST',
          content_type: 'application/json',
          data_type: 'json',
          data: req_data,
          success_func: function () {
            t.is_processing = false;
            t.selected_radius_buttons_block.balloon.close();
            window.gp_map.geoObjects.remove(
              t.selected_radius_buttons_block
            );
            t.selected_radius.setParent(null);
            t.selected_radius.editor.stopEditing();
            geoObj.initialPosition =
              geoObj.geometry.getCoordinates();
            t.editMarker.initialPosition = geoObj.initialPosition;
            geoObj.options.set('draggable', false);
            // обновим радиус и координаты точки
            geoObj.properties._data.radius = req_data.object.radius;
            geoObj.properties._data._lng = req_data.object.lng;
            geoObj.properties._data._lat = req_data.object.lat;
            geoObj.geometry.setCoordinates([
              req_data.object.lat,
              req_data.object.lng,
            ]);
            // marker.markers[markerIdx].properties._data.balloonContent.radius = req_data.object.radius;
            // marker.markers[markerIdx].properties._data.balloonContent.lat =  req_data.object.lat;
            // marker.markers[markerIdx].properties._data.balloonContent.lng = req_data.object.lng;
            _lat = req_data.object.lat;
            _lng = req_data.object.lng;
            radius = req_data.object.radius;
            t.movedMarkers.push({
              id: markerIdSave,
              coords: [
                _lat,
                _lng
              ]
            });
          },
          error_func: function () {
            t.is_processing = false;
          },
          bad_request_func: function () {
            t.is_processing = false;
          },
        });
      };

      const markersA = await markers;

      ymaps.ready(function () {
        clusterer = new ymaps.Clusterer({
          gridSize: 256,
          groupByCoordinates: false,
          hasBalloon: false,
          hasHint: false,
          margin: 10,
          maxZoom: 12,
          showInAlphabeticalOrder: false,
          viewportMargin: 128,
          zoomMargin: 0,
          clusterDisableClickZoom: false,
        });
        t.shopClusterer = clusterer;
        for (const marker of markersA) {
          points = [];
          for (let i = 0; i < marker.markers.length; i++) {
            points.push([
              marker.markers[i].geometry._coordinates[0],
              marker.markers[i].geometry._coordinates[1],
            ]);
          }
          geoObjects = [];
          for (let i = 0, len = points.length; i < len; i++) {
            const markerId =
              marker.markers[i].properties._data.balloonContent
                .id;
            const radius =
              marker.markers[i].properties._data.balloonContent
                .radius;
            const _lat =
              marker.markers[i].properties._data.balloonContent
                .lat;
            const _lng =
              marker.markers[i].properties._data.balloonContent
                .lng;
            const shop_network_id = marker.shop_network_id;
            const index =
              marker.markers[i].properties._data.balloonContent
                .indx;
            geoObjects[i] = new ymaps.Placemark(
              points[i],
              {
                markerId,
                radius,
                _lat,
                _lng,
                shop_network_id,
                markerIcon: marker.icon,
                i,
              },
              {
                iconLayout: 'default#imageWithContent',
                iconImageHref: marker.icon,
                iconImageSize: [32, 31],
                iconImageOffset: [-10, -33],
                hideIconOnBalloonOpen: false,
                balloonOffset: [-2, -30],
                openEmptyBalloon: true,
              }
            );

            let geoObj = geoObjects[i];

            geoObjects[i].events.add('click', ShowClick);
          }
          clusterer.add(geoObjects);
          if (geoObjects.length === 1) {
            geoObjects
          }
          window.gp_map.geoObjects.add(clusterer);
        }
      });
    },

    check_shop_id: function (shops, shop_id) {
      if (!shops) return false;

      for (var i = 0; i < shops.length; i++) {
        if (shops[i] == shop_id) return true;
      }
      return false;
    },

    onClickShow: function () {
      const t = this;
      ymaps.ready(async function () {
        $('.gp-point').hide();
        t.saveFilters();
        if (t.shopClusterer) {
          await t.shopClusterer.removeAll();
        }
        if (window.gp_map && window.gp_map.geoObjects) {
          await window.gp_map.geoObjects.removeAll();
        }
        t.networks_shops = [];
        t.loadGeopoints(0);
        t.DrawMapObjects();
        if (navigator.userAgent.indexOf('iPhone') != -1)
          $('.gp-filters').hide();
      });
    },

    calc_distance: function (lat1, lng1, lat2, lng2) {
      var R = 6371e3;
      var φ1 = this.to_radians(Number(lat1)),
        λ1 = this.to_radians(Number(lng1));
      var φ2 = this.to_radians(Number(lat2)),
        λ2 = this.to_radians(Number(lng2));
      var Δφ = φ2 - φ1;
      var Δλ = λ2 - λ1;

      var a =
        Math.sin(Δφ / 2) * Math.sin(Δφ / 2) +
        Math.cos(φ1) *
        Math.cos(φ2) *
        Math.sin(Δλ / 2) *
        Math.sin(Δλ / 2);
      var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

      return R * c;
    },

    to_radians: function (num) {
      return (num * Math.PI) / 180.0;
    },

    saveFilters: function () {
      var filters = {
        is_show_our_shops: this.ui.filter_show_our_shop.hasClass('on')
          ? '1'
          : '0',
        is_show_networks_shops: this.ui.filter_show_networks.hasClass(
          'on'
        )
          ? '1'
          : '0',
        is_show_problems: this.ui.filter_show_problems.hasClass('on')
          ? '1'
          : '0',
        region_id: +this.ui.filter_region.attr('data-id'),
        city_id: +this.ui.filter_city.attr('data-id'),
        shop_id: +this.ui.filter_shop.attr('data-id'),
        network_id: +this.ui.filter_network.attr('data-id'),
        geopoint_id: +this.ui.filter_address.attr('data-id'),
        radius_min: +this.ui.filter_radius.val().split(',')[0],
        radius_max: +this.ui.filter_radius.val().split(',')[1],
      };

      $.fn.make_filter_string(filters, this.page_hash);
    },

    restoreFilters: function () {
      this.filters = $.fn.parse_filter_string();

      if (this.filters.radius_min || this.filters.radius_max) {
        this.ui.filter_radius.slider(
          'setValue',
          [
            this.filters.radius_min ? +this.filters.radius_min : 0,
            this.filters.radius_max
              ? +this.filters.radius_max
              : 15000,
          ],
          true,
          true
        );
        if (this.filters.radius_min) delete this.filters.radius_min;
        if (this.filters.radius_max) delete this.filters.radius_max;
      }

      if (this.filters.is_show_our_shops) {
        if (+this.filters.is_show_our_shops) {
          this.ui.filter_show_our_shop.addClass('on');
          this.ui.filter_show_our_shop.removeClass('off');
        } else {
          this.ui.filter_show_our_shop.removeClass('on');
          this.ui.filter_show_our_shop.addClass('off');
        }
        delete this.filters.is_show_our_shops;
      }

      if (this.filters.is_show_networks_shops) {
        if (+this.filters.is_show_networks_shops) {
          this.ui.filter_show_networks.addClass('on');
          this.ui.filter_show_networks.removeClass('off');
        } else {
          this.ui.filter_show_networks.removeClass('on');
          this.ui.filter_show_networks.addClass('off');
        }
        delete this.filters.is_show_networks_shops;
      }

      if (this.filters.is_show_problems) {
        if (+this.filters.is_show_problems) {
          this.ui.filter_show_problems.addClass('on');
          this.ui.filter_show_problems.removeClass('off');
        } else {
          this.ui.filter_show_problems.removeClass('on');
          this.ui.filter_show_problems.addClass('off');
        }
        delete this.filters.is_show_problems;
      }

      if (this.filters.lat && this.filters.lng) {
        var lat = this.filters.lat,
          lng = this.filters.lng;
        setTimeout(function () {
          window.gp_map.setCenter([lat, lng]);
        }, 1000);
      }

      if (this.filters.lat) delete this.filters.lat;

      if (this.filters.lng) delete this.filters.lng;

      var t = this;
      var timer = setInterval(function () {
        if ($.isEmptyObject(t.filters)) {
          t.DrawMapObjects();
          clearInterval(timer);
        }
      }, 200);
    },
  });
});
