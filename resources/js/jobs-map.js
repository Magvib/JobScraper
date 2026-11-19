/**
 * Jobs map (Leaflet + marker clusters), the fullscreen expand mode and the
 * sidebar <-> marker hover linking for the jobs page.
 *
 * This used to be an inline <script> inside the jobs component, which
 * re-registered every document listener each time the page was visited
 * through wire:navigate. As a module it runs exactly once.
 *
 * The map is created lazily: a MutationObserver waits for #jobs-map (rendered
 * by the Livewire jobs island) and #jobs-map-data (JSON points) to appear.
 */
        (function () {
            function escapeHtml(str) {
                var div = document.createElement('div');
                div.textContent = str == null ? '' : String(str);
                return div.innerHTML;
            }

            function ensureLeaflet(callback) {
                if (window.L) {
                    return callback();
                }

                if (! window.jobsMapLeafletLoading) {
                    window.jobsMapLeafletLoading = new Promise(function (resolve) {
                        var link = document.createElement('link');
                        link.rel = 'stylesheet';
                        link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
                        document.head.appendChild(link);

                        var script = document.createElement('script');
                        script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
                        script.onload = function () {
                            // Cluster + spiderfy markers that share the same city coordinates.
                            // The cluster icon is built from scratch via iconCreateFunction
                            // below, so the plugin's default stylesheets aren't loaded —
                            // everything it needs lives in this single style block.
                            var clusterStyle = document.createElement('style');
                            clusterStyle.textContent =
                                // Cluster bubbles: same dot design as the job markers,
                                // scaled up with the job count inside.
                                '.job-cluster-icon { background: none; border: none; }' +
                                '.job-cluster {' +
                                    'border-radius: 50%;' +
                                    'background: #1d4ed8;' +
                                    'border: 3px solid #fff;' +
                                    'box-shadow: 0 0 0 2px rgba(0, 0, 0, 0.15), 0 2px 8px rgba(0, 0, 0, 0.3);' +
                                    'color: #fff;' +
                                    'font-weight: 800;' +
                                    'font-size: 13px;' +
                                    'text-align: center;' +
                                    'text-shadow: 0 1px 2px rgba(0, 0, 0, 0.4);' +
                                    'transition: transform 0.15s ease;' +
                                '}' +
                                '.job-cluster-icon:hover .job-cluster {' +
                                    'transform: scale(1.1);' +
                                '}' +
                                // Distinct pulsing dot for the user's own location,
                                // so it stands apart from the blue job markers.
                                '.user-location-icon { background: none; border: none; }' +
                                '.user-location-dot {' +
                                    'width: 16px;' +
                                    'height: 16px;' +
                                    'border-radius: 50%;' +
                                    'background: #16a34a;' +
                                    'border: 3px solid #fff;' +
                                    'box-shadow: 0 0 0 2px rgba(22, 163, 74, 0.4), 0 2px 6px rgba(0, 0, 0, 0.3);' +
                                    'animation: user-location-pulse 2s infinite;' +
                                '}' +
                                '@keyframes user-location-pulse {' +
                                    '0% { box-shadow: 0 0 0 0 rgba(22, 163, 74, 0.5), 0 2px 6px rgba(0, 0, 0, 0.3); }' +
                                    '70% { box-shadow: 0 0 0 14px rgba(22, 163, 74, 0), 0 2px 6px rgba(0, 0, 0, 0.3); }' +
                                    '100% { box-shadow: 0 0 0 0 rgba(22, 163, 74, 0), 0 2px 6px rgba(0, 0, 0, 0.3); }' +
                                '}' +
                                // Job markers: smaller blue dots in the same family as the
                                // cluster color, with a hover scale so they feel interactive.
                                '.job-marker-icon { background: none; border: none; }' +
                                '.job-marker-dot {' +
                                    'width: 16px;' +
                                    'height: 16px;' +
                                    'border-radius: 50%;' +
                                    'background: #1d4ed8;' +
                                    'border: 3px solid #fff;' +
                                    'box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.2), 0 1px 4px rgba(0, 0, 0, 0.3);' +
                                    'transition: transform 0.15s ease;' +
                                '}' +
                                '.job-marker-icon:hover .job-marker-dot {' +
                                    'transform: scale(1.4);' +
                                '}' +
                                // Hovering a job in the fullscreen sidebar pings its
                                // position on the map with a pulsing ring, and scales
                                // up the dot — or the cluster bubble that currently
                                // represents it — while the hover lasts.
                                '.jobs-map-highlight-icon { background: none; border: none; pointer-events: none; }' +
                                '.jobs-map-highlight-ring {' +
                                    'width: 36px;' +
                                    'height: 36px;' +
                                    'border-radius: 50%;' +
                                    'border: 3px solid #1d4ed8;' +
                                    'box-sizing: border-box;' +
                                    'animation: jobs-map-highlight-pulse 1.2s ease-out infinite;' +
                                '}' +
                                '@keyframes jobs-map-highlight-pulse {' +
                                    '0% { transform: scale(0.4); opacity: 1; }' +
                                    '100% { transform: scale(1.2); opacity: 0; }' +
                                '}' +
                                '.job-marker-dot.is-active {' +
                                    'transform: scale(1.9);' +
                                    'box-shadow: 0 0 0 5px rgba(29, 78, 216, 0.35), 0 1px 4px rgba(0, 0, 0, 0.3);' +
                                '}' +
                                '.job-cluster.is-active {' +
                                    'transform: scale(1.15);' +
                                    'box-shadow: 0 0 0 5px rgba(29, 78, 216, 0.35), 0 2px 8px rgba(0, 0, 0, 0.3);' +
                                '}';
                            document.head.appendChild(clusterStyle);

                            var clusterScript = document.createElement('script');
                            clusterScript.src = 'https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js';
                            clusterScript.onload = resolve;
                            document.head.appendChild(clusterScript);
                        };
                        document.head.appendChild(script);
                    });
                }

                window.jobsMapLeafletLoading.then(callback);
            }

            // Fullscreen state lives on window (not Livewire) so expanding is
            // instant with no server round trip. Livewire morphs can strip the
            // runtime classes, so re-apply them after every commit and every
            // map re-init (jobsMapSetFullscreen is idempotent).
            window.jobsMapExpanded = false;

            function jobsMapFilterSidebar(map) {
                var list = document.getElementById('jobs-map-sidebar-list');

                if (! list) {
                    return;
                }

                var bounds = map.getBounds();
                var count = 0;

                list.querySelectorAll('.jobs-map-sidebar-item').forEach(function (item) {
                    var lat = parseFloat(item.getAttribute('data-map-lat'));
                    var lng = parseFloat(item.getAttribute('data-map-lng'));
                    var inView = bounds.contains([lat, lng]);

                    item.classList.toggle('hidden', ! inView);

                    if (inView) {
                        count++;
                    }
                });

                var countEl = document.getElementById('jobs-map-sidebar-count');

                if (countEl) {
                    countEl.textContent = count;
                }

                var empty = document.getElementById('jobs-map-sidebar-empty');

                if (empty) {
                    empty.classList.toggle('hidden', count > 0);
                }
            }

            function jobsMapClearJobHighlight() {
                if (window.jobsMap && window.jobsMapHighlight) {
                    window.jobsMap.removeLayer(window.jobsMapHighlight);
                    window.jobsMapHighlight = null;
                }

                document.querySelectorAll('.job-marker-dot.is-active, .job-cluster.is-active').forEach(function (el) {
                    el.classList.remove('is-active');
                });
            }

            /**
             * Highlight the map position for a hovered sidebar job: a pulsing
             * ring at its coordinates, plus a scaled-up dot — or the cluster
             * bubble that currently represents it when the marker is inside
             * a cluster.
             */
            function jobsMapHighlightJob(lat, lng, color) {
                jobsMapClearJobHighlight();

                var map = window.jobsMap;

                if (! map) {
                    return;
                }

                var ringIcon = L.divIcon({
                    className: 'jobs-map-highlight-icon',
                    html: '<div class="jobs-map-highlight-ring" style="border-color:' + (color || '#1d4ed8') + '"></div>',
                    iconSize: [36, 36],
                    iconAnchor: [18, 18]
                });

                window.jobsMapHighlight = L.marker([lat, lng], { icon: ringIcon, interactive: false, zIndexOffset: 2000 }).addTo(map);

                (window.jobsMapJobMarkers || []).forEach(function (entry) {
                    // Coordinates come from the same source on both sides, but
                    // compare with a tolerance so float formatting can't miss.
                    if (Math.abs(entry.lat - lat) > 1e-9 || Math.abs(entry.lng - lng) > 1e-9) {
                        return;
                    }

                    var visible = entry.cluster.getVisibleParent(entry.marker);

                    if (! visible) {
                        return;
                    }

                    var el = visible.getElement();

                    if (! el) {
                        return;
                    }

                    var target = el.querySelector('.job-marker-dot') || el.querySelector('.job-cluster');

                    if (target) {
                        target.classList.add('is-active');
                    }
                });
            }

            /**
             * Sidebar items that correspond to the given map markers. Markers
             * match by coordinates (same source on both sides, compared with
             * a tolerance), so one marker matches every job in the same spot.
             */
            function jobsMapSidebarItemsForMarkers(markers) {
                var list = document.getElementById('jobs-map-sidebar-list');

                if (! list) {
                    return [];
                }

                var entries = (window.jobsMapJobMarkers || []).filter(function (entry) {
                    return markers.indexOf(entry.marker) !== -1;
                });

                var items = [];

                if (! entries.length) {
                    return items;
                }

                list.querySelectorAll('.jobs-map-sidebar-item').forEach(function (item) {
                    var lat = parseFloat(item.getAttribute('data-map-lat'));
                    var lng = parseFloat(item.getAttribute('data-map-lng'));

                    var matches = entries.some(function (entry) {
                        return Math.abs(entry.lat - lat) < 1e-9 && Math.abs(entry.lng - lng) < 1e-9;
                    });

                    if (matches) {
                        items.push(item);
                    }
                });

                return items;
            }

            function jobsMapClearSidebarHover() {
                (window.jobsMapSidebarHoverItems || []).forEach(function (item) {
                    item.classList.remove('is-highlighted');
                });

                window.jobsMapSidebarHoverItems = [];
            }

            // Hovering a marker (or cluster) on the map highlights its sidebar
            // item(s) and scrolls the first one into view.
            function jobsMapHoverSidebar(markers) {
                jobsMapClearSidebarHover();

                if (! window.jobsMapExpanded) {
                    return;
                }

                window.jobsMapSidebarHoverItems = jobsMapSidebarItemsForMarkers(markers);

                window.jobsMapSidebarHoverItems.forEach(function (item, index) {
                    item.classList.add('is-highlighted');

                    if (index === 0) {
                        item.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
                    }
                });
            }

            // Clicking a marker selects its sidebar item(s); the selection stays
            // until another marker is clicked, empty map area is clicked or
            // fullscreen is exited.
            function jobsMapSelectSidebar(markers) {
                (window.jobsMapSidebarSelectedItems || []).forEach(function (item) {
                    item.classList.remove('is-selected');
                });

                window.jobsMapSidebarSelectedItems = jobsMapSidebarItemsForMarkers(markers);

                window.jobsMapSidebarSelectedItems.forEach(function (item, index) {
                    item.classList.add('is-selected');

                    if (index === 0 && window.jobsMapExpanded) {
                        item.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
                    }
                });
            }

            document.addEventListener('mouseover', function (e) {
                var item = e.target.closest('.jobs-map-sidebar-item');

                if (! item || item.contains(e.relatedTarget)) {
                    return;
                }

                jobsMapHighlightJob(
                    parseFloat(item.getAttribute('data-map-lat')),
                    parseFloat(item.getAttribute('data-map-lng')),
                    item.getAttribute('data-map-color')
                );
            });

            document.addEventListener('mouseout', function (e) {
                var item = e.target.closest('.jobs-map-sidebar-item');

                if (! item || item.contains(e.relatedTarget)) {
                    return;
                }

                jobsMapClearJobHighlight();
            });

            function jobsMapSetFullscreen(active) {
                window.jobsMapExpanded = active;

                if (! active) {
                    jobsMapClearJobHighlight();
                    jobsMapClearSidebarHover();
                    jobsMapSelectSidebar([]);
                }

                var card = document.getElementById('jobs-map-card');

                document.body.classList.toggle('jobs-map-fullscreen-open', active);

                if (card) {
                    card.classList.toggle('is-fullscreen', active);
                }

                // Leaflet must re-measure after the layout change, and the
                // sidebar needs an initial filter for the current viewport.
                requestAnimationFrame(function () {
                    if (window.jobsMap) {
                        try {
                            window.jobsMap.invalidateSize();
                        } catch (err) {
                            // The map may have been removed by a concurrent re-init.
                        }

                        if (active) {
                            jobsMapFilterSidebar(window.jobsMap);
                        }
                    }
                });
            }

            document.addEventListener('click', function (e) {
                if (e.target.closest('#jobs-map-expand')) {
                    jobsMapSetFullscreen(true);
                }

                if (e.target.closest('#jobs-map-exit')) {
                    jobsMapSetFullscreen(false);
                }
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && window.jobsMapExpanded) {
                    jobsMapSetFullscreen(false);
                }
            });

            function registerLivewireHook() {
                Livewire.hook('commit', function ({ succeed }) {
                    succeed(function () {
                        if (window.jobsMapExpanded) {
                            requestAnimationFrame(function () {
                                jobsMapSetFullscreen(true);
                            });
                        }
                    });
                });
            }

            // This module can run before or after Livewire has booted.
            if (window.Livewire) {
                registerLivewireHook();
            } else {
                document.addEventListener('livewire:init', registerLivewireHook);
            }

            function initJobsMap(container, points) {
                ensureLeaflet(function () {
                    // A re-render replaces the card, which loses the fullscreen
                    // classes — restore them before the map is created so it
                    // measures at full size from the start.
                    if (window.jobsMapExpanded) {
                        var freshCard = container.closest('#jobs-map-card');

                        if (freshCard) {
                            freshCard.classList.add('is-fullscreen');
                        }

                        document.body.classList.add('jobs-map-fullscreen-open');
                    }

                    if (window.jobsMap) {
                        window.jobsMap.remove();
                        window.jobsMap = null;
                    }

                    // Rebuilt below from the markers, so sidebar hover can find
                    // the marker (or cluster) that represents a given location.
                    window.jobsMapJobMarkers = [];
                    window.jobsMapHighlight = null;

                    var map = L.map(container, { attributionControl: false });
                    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 18,
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a> contributors'
                    }).addTo(map);

                    // maxClusterRadius: 0 disables distance-based clustering entirely,
                    // so only markers sharing (nearly) the same coordinates group up —
                    // e.g. jobs in the same city — and everything else stays a single
                    // marker no matter how far the map is zoomed out.
                    var cluster = L.markerClusterGroup({
                        maxClusterRadius: 18,
                        iconCreateFunction: function (group) {
                            var count = group.getChildCount();
                            var size = count < 10 ? 36 : count < 50 ? 44 : 52;

                            // Count the markers per keyword color and split the
                            // cluster background like a pie chart: each keyword
                            // gets a share of the circle proportional to its
                            // number of jobs (2 green + 1 blue -> 2/3 green,
                            // 1/3 blue). A single keyword (or none) stays a
                            // solid color.
                            var colorCounts = {};
                            var colors = [];
                            var total = 0;

                            group.getAllChildMarkers().forEach(function (marker) {
                                var color = marker.options.keywordColor || '#1d4ed8';

                                if (colorCounts[color] === undefined) {
                                    colorCounts[color] = 0;
                                    colors.push(color);
                                }

                                colorCounts[color]++;
                                total++;
                            });

                            var background;

                            if (colors.length > 1) {
                                var stops = [];
                                var cumulative = 0;

                                colors.forEach(function (color) {
                                    var start = cumulative / total * 100;
                                    cumulative += colorCounts[color];
                                    stops.push(color + ' ' + start.toFixed(2) + '% ' + (cumulative / total * 100).toFixed(2) + '%');
                                });

                                background = 'conic-gradient(' + stops.join(', ') + ')';
                            } else {
                                background = colors[0] || '#1d4ed8';
                            }

                            return L.divIcon({
                                className: 'job-cluster-icon',
                                html: '<div class="job-cluster" style="width:' + size + 'px;height:' + size + 'px;line-height:' + (size - 6) + 'px;background:' + background + '">' + count + '</div>',
                                iconSize: L.point(size, size)
                            });
                        }
                    });

                    points.forEach(function (point) {
                        var jobIcon = L.divIcon({
                            className: 'job-marker-icon',
                            html: '<div class="job-marker-dot"' + (point.color ? ' style="background:' + point.color + '"' : '') + '></div>',
                            iconSize: [16, 16],
                            iconAnchor: [8, 8]
                        });

                        var marker = L.marker([point.lat, point.lng], { icon: jobIcon, keywordColor: point.color })
                            .bindPopup(
                                '<div style="min-width:200px">' +
                                    '<a href="' + escapeHtml(point.url) + '" target="_blank" rel="noopener" style="font-weight:600">' + escapeHtml(point.title) + '</a>' +
                                    '<div style="font-size:12px;margin-top:2px">' + escapeHtml(point.company) +
                                        (point.color
                                            ? ' - <span style="color:' + point.color + ';font-weight:600">' + escapeHtml(point.keyword) + '</span>'
                                            : (point.keyword ? ' - ' + escapeHtml(point.keyword) : '')) +
                                    '</div>' +
                                    '<div style="font-size:12px;opacity:.65">' + escapeHtml(point.location) + '</div>' +
                                '</div>'
                            );

                        cluster.addLayer(marker);

                        // Hover and click on the map highlight/select the job's
                        // sidebar item — the reverse of the sidebar hover ping.
                        marker.on('mouseover', function () {
                            jobsMapHoverSidebar([marker]);
                        });

                        marker.on('mouseout', jobsMapClearSidebarHover);

                        marker.on('click', function () {
                            jobsMapSelectSidebar([marker]);
                        });

                        window.jobsMapJobMarkers.push({
                            lat: point.lat,
                            lng: point.lng,
                            marker: marker,
                            cluster: cluster
                        });
                    });

                    map.addLayer(cluster);

                    // A cluster bubble represents several markers, so hover
                    // highlights every sidebar item inside it.
                    cluster.on('clustermouseover', function (e) {
                        jobsMapHoverSidebar(e.layer.getAllChildMarkers());
                    });

                    cluster.on('clustermouseout', jobsMapClearSidebarHover);

                    // The user's own location, kept out of the job cluster so it
                    // never merges with job markers and always stays on top.
                    var userMarker = null;
                    var userData = document.getElementById('jobs-map-user-data');
                    var userPoint = userData ? JSON.parse(userData.textContent) : null;

                    if (userPoint) {
                        var userIcon = L.divIcon({
                            className: 'user-location-icon',
                            html: '<div class="user-location-dot"></div>',
                            iconSize: [16, 16],
                            iconAnchor: [8, 8]
                        });

                        userMarker = L.marker([userPoint.lat, userPoint.lng], { icon: userIcon, zIndexOffset: 1000 })
                            .bindPopup(
                                '<div style="min-width:150px">' +
                                    '<span style="font-weight:600">' + escapeHtml(userPoint.city || 'Your location') + '</span>' +
                                    '<div style="font-size:12px;opacity:.65">Your location</div>' +
                                    (userPoint.maxDistance ? '<div style="font-size:12px;opacity:.65">' + userPoint.maxDistance + ' km search radius</div>' : '') +
                                '</div>'
                            )
                            .addTo(map);

                        // The user's job-search radius (max_distance, in km) as a
                        // dashed circle around their location.
                        if (userPoint.maxDistance) {
                            var radiusCircle = L.circle([userPoint.lat, userPoint.lng], {
                                radius: userPoint.maxDistance * 1000,
                                color: '#16a34a',
                                weight: 2,
                                opacity: 0.7,
                                dashArray: '6 8',
                                fillColor: '#16a34a',
                                fillOpacity: 0.06,
                                // Non-interactive so clicks pass through to the job
                                // markers inside the circle.
                                interactive: false
                            }).addTo(map);

                            userMarker.radiusCircle = radiusCircle;
                        }
                    }

                    var bounds = cluster.getBounds();

                    if (userMarker) {
                        bounds.extend(userMarker.getLatLng());
                    }

                    if (points.length + (userMarker ? 1 : 0) > 1) {
                        map.fitBounds(bounds.pad(0.15));
                    } else if (userMarker) {
                        map.setView([userPoint.lat, userPoint.lng], 12);
                    } else {
                        map.setView([points[0].lat, points[0].lng], 12);
                    }

                    window.jobsMap = map;

                    // Clicking empty map area expands to fullscreen. Clicks on
                    // markers, clusters, popups and controls have their own
                    // handlers and are excluded so links keep working.
                    map.on('click', function (e) {
                        var target = e.originalEvent && e.originalEvent.target;

                        if (target && target.closest && target.closest('.leaflet-popup, .leaflet-control')) {
                            return;
                        }

                        // Clicking empty map area while fullscreen clears the
                        // current selection; markers stop propagation so their
                        // clicks never reach this handler.
                        if (window.jobsMapExpanded) {
                            jobsMapSelectSidebar([]);
                        }

                        jobsMapSetFullscreen(true);
                    });

                    // Panning or zooming re-filters the sidebar to the jobs
                    // that are currently in view.
                    map.on('moveend', function () {
                        if (window.jobsMapExpanded) {
                            jobsMapFilterSidebar(map);
                        }
                    });

                    if (window.jobsMapExpanded) {
                        requestAnimationFrame(function () {
                            map.invalidateSize();
                            jobsMapFilterSidebar(map);
                        });
                    }
                });
            }

            var mapObserver = new MutationObserver(function () {
                var container = document.getElementById('jobs-map');

                if (! container || container.dataset.initialized) {
                    return;
                }

                var data = document.getElementById('jobs-map-data');

                if (! data) {
                    return;
                }

                container.dataset.initialized = '1';
                initJobsMap(container, JSON.parse(data.textContent));
            });

            mapObserver.observe(document.body, { childList: true, subtree: true });
        })();
