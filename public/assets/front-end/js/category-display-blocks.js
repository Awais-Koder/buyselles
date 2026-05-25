'use strict';

(function () {
    function initLocationSelects(root) {
        root.querySelectorAll('.cdb-location-init').forEach(function (initEl) {
            var prefix = initEl.dataset.prefix;
            var countryEl = document.getElementById(prefix + '-country');
            var cityEl = document.getElementById(prefix + '-city');
            var areaEl = document.getElementById(prefix + '-area');
            if (!countryEl) {
                return;
            }

            var citiesUrl = initEl.dataset.citiesUrl;
            var areasUrl = initEl.dataset.areasUrl;

            function loadCities(countryId, selectedCityId, callback) {
                if (!countryId) {
                    cityEl.innerHTML = '<option value="">--- All cities ---</option>';
                    cityEl.disabled = true;
                    areaEl.innerHTML = '<option value="">--- All areas ---</option>';
                    areaEl.disabled = true;
                    return;
                }
                fetch(citiesUrl.replace(':id', countryId))
                    .then(function (r) { return r.json(); })
                    .then(function (cities) {
                        var html = '<option value="">--- All cities ---</option>';
                        cities.forEach(function (c) {
                            html += '<option value="' + c.id + '"' + (String(c.id) === String(selectedCityId) ? ' selected' : '') + '>' + c.name + '</option>';
                        });
                        cityEl.innerHTML = html;
                        cityEl.disabled = false;
                        if (callback) {
                            callback();
                        }
                    });
            }

            function loadAreas(cityId, selectedAreaId) {
                if (!cityId) {
                    areaEl.innerHTML = '<option value="">--- All areas ---</option>';
                    areaEl.disabled = true;
                    return;
                }
                fetch(areasUrl.replace(':id', cityId))
                    .then(function (r) { return r.json(); })
                    .then(function (areas) {
                        var html = '<option value="">--- All areas ---</option>';
                        areas.forEach(function (a) {
                            html += '<option value="' + a.id + '"' + (String(a.id) === String(selectedAreaId) ? ' selected' : '') + '>' + a.name + '</option>';
                        });
                        areaEl.innerHTML = html;
                        areaEl.disabled = false;
                    });
            }

            countryEl.addEventListener('change', function () {
                loadCities(this.value, '', function () {
                    areaEl.innerHTML = '<option value="">--- All areas ---</option>';
                    areaEl.disabled = true;
                });
            });

            cityEl.addEventListener('change', function () {
                loadAreas(this.value, '');
            });

            if (initEl.dataset.country) {
                loadCities(initEl.dataset.country, initEl.dataset.city || '', function () {
                    if (initEl.dataset.city) {
                        loadAreas(initEl.dataset.city, initEl.dataset.area || '');
                    }
                });
            }
        });
    }

    function collectParams(blockEl) {
        var params = new URLSearchParams();
        var searchInput = blockEl.querySelector('.cdb-search-input');
        if (searchInput && searchInput.value.trim()) {
            params.set('search', searchInput.value.trim());
        }

        var prefix = blockEl.querySelector('.category-display-location-filters');
        if (prefix) {
            var blockPrefix = prefix.dataset.blockPrefix;
            var country = document.getElementById(blockPrefix + '-country');
            var city = document.getElementById(blockPrefix + '-city');
            var area = document.getElementById(blockPrefix + '-area');
            if (country && country.value) {
                params.set('country_id', country.value);
            }
            if (city && city.value) {
                params.set('city_id', city.value);
            }
            if (area && area.value) {
                params.set('area_id', area.value);
            }
        }

        return params;
    }

    function loadBlock(blockEl) {
        var url = blockEl.dataset.ajaxUrl;
        var content = blockEl.querySelector('.cdb-ajax-content');
        if (!url || !content) {
            return;
        }

        var params = collectParams(blockEl);
        content.classList.add('opacity-50');

        fetch(url + (params.toString() ? '?' + params.toString() : ''))
            .then(function (r) { return r.json(); })
            .then(function (data) {
                content.innerHTML = data.html || '';
                content.classList.remove('opacity-50');
            })
            .catch(function () {
                content.classList.remove('opacity-50');
            });
    }

    document.querySelectorAll('.category-display-ajax-block').forEach(function (blockEl) {
        initLocationSelects(blockEl);

        blockEl.querySelectorAll('.cdb-apply-filter').forEach(function (btn) {
            btn.addEventListener('click', function () {
                loadBlock(blockEl);
            });
        });

        var searchInput = blockEl.querySelector('.cdb-search-input');
        if (searchInput) {
            searchInput.addEventListener('keypress', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    loadBlock(blockEl);
                }
            });
        }
    });
})();
