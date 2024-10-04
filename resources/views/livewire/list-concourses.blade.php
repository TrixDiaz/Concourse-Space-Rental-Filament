<div>
    <div id="map" style="height: 400px; width: 100%;"></div>
    {{ $this->table }}
</div>

@push('scripts')
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA64WIgsJoT70A83moLEvuhFwwV6R-15Wg&callback=initMap" async defer></script>
<script>
    function initMap() {
        const map = new google.maps.Map(document.getElementById("map"), {
            zoom: 10,
            center: {
                lat: 0,
                lng: 0
            },
            mapTypeId: 'satellite',
            mapTypeControl: false,
            zoomControl: false,
            streetViewControl: false,
            fullscreenControl: false,
            clickableIcons: false,
            styles: [{
                featureType: 'all',
                elementType: 'all',
                stylers: [{
                    visibility: 'on'
                }]
            }],
            disableDefaultUI: true,
            zoomControl: true,
            zoomControlOptions: {
                position: google.maps.ControlPosition.TOP_LEFT,
            },
        });

        const concourses = @json($concourses);
        const bounds = new google.maps.LatLngBounds();

        concourses.forEach((concourse) => {
            if (concourse.lat && concourse.lng) {
                const marker = new google.maps.Marker({
                    position: {
                        lat: parseFloat(concourse.lat),
                        lng: parseFloat(concourse.lng)
                    },
                    map: map,
                    title: concourse.name,
                });

                bounds.extend(marker.getPosition());

                const infoWindow = new google.maps.InfoWindow({
                    content: `<x-filament::section>
                                <x-slot name="heading">
                                      ${concourse.name}
                                </x-slot>
                                                    
                                <x-slot name="description">
                                 <x-filament::badge>
                                    ${concourse.address}
                                      </x-filament::badge>   
                                </x-slot>
                            </x-filament::section>
                             `,
                });

                marker.addListener("click", () => {
                    infoWindow.open({
                        anchor: marker,
                        map,
                        shouldFocus: true,
                    });
                });
            }
        });

        map.fitBounds(bounds);
    }
</script>
@endpush