<template>
<div @click="activatePan()" class="map" id="vue-frontend-app">
    <!-- zoom buttons -->
    <div class="zoomer">
        <button @click="zoomInOut('+')">+</button>
        <button @click="zoomInOut('-')">-</button>
    </div>
    <!-- MAP TITLE -->
    <div class="map__title text-green" :style="{'transform': 'scale('+counterZoom+')'}">
        <div style="">
            <span class="map__title__leaf" :class="showFooter ? 'bigLeafActive' : null" v-html="iconLeaf"></span>
        </div>
        <fade-transition>
            <span v-show="!showFooter">
                <h1>THIS IS</h1>
                <h1>YOUR</h1>
                <h1>LINMERE</h1>
            </span>
        </fade-transition>
    </div>

    <panZoom @init="onInit" :options="{zoomDoubleClickSpeed: 1, smoothScroll: false, selector: '#mapBackground', onDoubleClick: onDoubleClickHandler, beforeWheel: beforeWheelHandler, autocenter: screenWidth >= 1920 ? false : true, bounds: true, initialY: screenWidth >= 1920 ? 0 : null, initialX: screenWidth >= 1920 ? -3 : null, initialZoom: screenWidth >= 1920 ? 1 : null}">
        <div class="mapBackground" id="mapBackground" :style="[{'background-image': 'url(' + backgroundImage + ')'}]">
            <transitionGroup name="bounce" mode="inOut">
                <template v-for="(cat) in categorys" style="border: 1px red dashed">
                    <div :data-category="cat.title" :data-location="location.title" v-show="!category_selected || category_selected == cat" v-for="(location) in cat.locations" :key="location.title" class="locations__marker" :style="{top:location.y+'px', left:+location.x+'px'}">
                        <div class="locations__marker__icon clickMe" :style="{backgroundColor:cat.iconColor}" v-html="cat.icon"></div>
                        <h4 class="locations__marker__title clickMe">{{location.title}}</h4>
                    </div>
                </template>
            </transitionGroup>
        </div>
    </panZoom>
    <!-- MAP ELEMENTS END -->

    <!--INFO WINDOW -->
    <fade-transition>
        <div v-if="Object.keys(location_selected).length && screenWidth > 1920" class="infoWindow" :style="{left: location_selected.x+'px'}">
            <button class="infoWindow__close" @click="popUp()">
                <div v-html="iconClose"></div>
            </button>
            <h3 class="infoWindow__title">{{location_selected.longTitle}}</h3>
            <div style="position: relative">
                <span v-html="iconVArrow" class="arrows left" @click="() => {this.$refs['slider'] && this.$refs['slider'].$emit('slidePre')}"></span>
                <span v-html="iconVArrow" class="arrows right" @click="() => {this.$refs['slider'] && this.$refs['slider'].$emit('slideNext')}"></span>
                <slider class="infoWindow__slider" ref="slider" :options="options">
                    <slideritem v-for="(image,i) in location_selected.images" :key="i">
                        <img :src="image.sizes.large">
                    </slideritem>
                    <div slot="loading">loading...</div>
                </slider>
            </div>
            <div class="infoWindow__inner">
                <span v-for="(tab, key, i) in tabs" :key="i" class="infoWindow__inner__item" @click="selectTab(key)">
                    <div class="infoWindow__inner__item__arrow" v-html="iconVArrow" :style="tab ? 'transform: rotate(180deg)':null"></div>
                    <h3 class="infoWindow__inner__item__title">
                        {{key.replaceAll('_',' ')}}
                    </h3>
                    <collapse-transition>
                        <div v-if="tab" class="infoWindow__inner__item__content">
                            <div style="height: 17px; width: 100%; display: block"></div>
                            <p v-html="location_selected.description">
                            </p>
                        </div>
                    </collapse-transition>
                </span>
            </div>
        </div>
    </fade-transition>
    <!-- INFO WINDOW END -->

    <!--INFO WINDOW SMALL-->
    <slide-y-down-transition>
        <div v-if="Object.keys(location_selected).length && screenWidth < 1920" class="infoWindowSmall">
            <button class="infoWindow__close" @click="popUp()">
                <div class="darkBtn" v-html="iconClose"></div>
            </button>
            <img :src="location_selected.images[0].sizes.thumbnail">
            <div class="infoWindowSmall__inner">
                <h3>{{location_selected.longTitle}}</h3>
                <p v-html="location_selected.description"></p>

            </div>
        </div>
    </slide-y-down-transition>
    <!--INFO WINDOW SMALL END-->

    <!-- FOOTER -->
    <slide-y-down-transition>
        <div v-show="showFooter || screenWidth < 1920" id="map__footer" class="map__footer">
            <div v-for="(cat, i) in categorys" :key="i" class="map__footer__item" :class="cat == category_selected ? 'activeIcon' : 'inactiveIcon' " @click="category_selected = cat">
                <div v-html="cat.icon"></div>
                <p v-if="screenWidth < 1920">{{cat.title.replace('_',' ')}}</p>
                <h3 v-else>{{cat.title.replace('_',' ')}}</h3>
            </div>
        </div>
    </slide-y-down-transition>
    <slide-x-left-transition>
        <div v-if="screenWidth > 1920" class="filterButton" @click="showFooter = !showFooter">
            <h3 class="text-green" v-show="!showFooter">FILTER</h3>
            <button v-html="iconVArrow" :class="showFooter ? 'bg-pink' :'bg-green'" style="padding: 18px" class="btn-sq inactiveIcon" :style="!showFooter ? 'transform: rotate(270deg)' : 'transform: rotate(90deg)'"></button>
        </div>
    </slide-x-left-transition>
    <!-- FOOTER END -->
</div>
</template>

<script>
import { FadeTransition, SlideXLeftTransition, SlideYDownTransition, CollapseTransition, SlideYUpTransition } from 'vue2-transitions'
import { slider, slideritem } from 'vue-concise-slider'
import axios from 'axios';
import panZoom from 'vue-panzoom'
import VueScreenSize from 'vue-screen-size'
import Vue from 'vue'
// install plugin
Vue.use(panZoom);
Vue.use(VueScreenSize)
export default {
    name: 'App',
    methods: {
        onDoubleClickHandler(e) {
            //alert('here')
            // `e` - is current double click event.
            return false // tells the library to not preventDefault, and not stop propagation
        },
        beforeWheelHandler(e) {
            if (this.screenWidth < 768) {
                var shouldIgnore = !e.altKey;
                return shouldIgnore;
            }
        },
        resizeMap() {
            if (this.screenWidth < 800) {
                this.panner.smoothMoveTo(this.panner.getTransform().x, 50)
            } else {
                this.panner.smoothMoveTo(this.panner.getTransform().x, 0)
            }
        },
        activatePan() {
            this.panner.resume();
        },
        deactivatePan() {
            this.panner.pause();
        },
        getDimensions() {
            this.windowWidth = document.getElementById('vue-frontend-app').offsetWidth;
            this.windowHeight = document.getElementById('vue-frontend-app').offsetHeight;
        },
        onInit(panzoomInstance, id) {
            this.panner = panzoomInstance
            this.panner.setTransformOrigin({ x: 0.5, y: 0.5 })
            this.panner.setZoomSpeed(0.01)
            this.resizeMap()
            this.deactivatePan()

            this.panner.on('panstart', function (e) {
                document.getElementById("mapBackground").style.cursor = "grabbing"
            });

            this.panner.on('panend', (e) => {

                document.getElementById("mapBackground").style.cursor = "grab"
                //   this.deactivatePan()

            });

            setTimeout(() => {
                const boxes = document.querySelectorAll('.locations__marker');
                let x = this
                boxes.forEach(box => {
                    box.addEventListener('click', function handleClick(event) {
                        console.log('click received')
                        x.popUp(event.target.closest("[data-location]").dataset.category, event.target.closest("[data-location]").dataset.location)

                    });

                    box.addEventListener('touchstart', function handleClick(event) {
                        console.log('touch received')
                        x.popUp(event.target.closest("[data-location]").dataset.category, event.target.closest("[data-location]").dataset.location)

                    });
                });
            }, 2000);
        },
        zoomInOut(inOut) {
            let scale = this.panner.getTransform().scale
            var y = document.getElementById('vue-frontend-app').clientHeight / 2
            var x = document.getElementById('vue-frontend-app').clientWidth / 2
            console.log(scale)
            if (inOut == '+' && scale <= 1.0393117383747408) {
                this.panner.smoothZoom(x, y, 1.4);
            }
            if (inOut == '-' && scale >= 0.23659892565807147) {
                this.panner.smoothZoom(x, y, 0.6);
            }
        },
        popUp(cat, location) {

            if (cat && location) {
                let locations = this.categorys.find(x => x.title == cat).locations
                let data = locations.find(y => y.title == location)
                this.location_selected = data
            } else {
                this.location_selected = []
            }

        },
        selectTab(key) {
            this.closeAllTabs()
            this.tabs[key] = !this.tabs[key]
        },
        closeAllTabs() {
            Object.keys(this.tabs).forEach(key => {
                this.tabs[key] = false;
            });
        }
    },
    computed: {
        kioskMode() {
            return window.location.href.indexOf("kiosk") > -1 ? true : false
        },
        screenWidth() {
            return this.$vssWidth
        },
        screenHeight() {
            return this.$vssHeight
        },
    },
    watch: {
        showFooter() {
            this.location_selected = []
            this.category_selected = ''
        },
        location_selected() {
            this.closeAllTabs()
        },
    },
    components: {
        slider,
        slideritem,
        SlideXLeftTransition,
        FadeTransition,
        SlideYDownTransition,
        SlideYUpTransition,
        CollapseTransition
    },

    async created() {

        //  alert(this.screenWidth)
        let page = await axios.get("https://linmere.greenwich-design-projects.co.uk/wp-json/wp/v2/pages")
        let data = await page.data.find(page => page.slug == 'kiosk').acf
        this.backgroundImage = data.background_map_image
        // // houses
        // if (data['houses'])
        //     data['houses'].forEach((house) => {
        //         this.categorys.find(x => x.title == 'houses').locations.push(house.house)
        //     })
        // schools
        if (data['schools'])
            data['schools'].forEach((school) => {
                this.categorys.find(x => x.title == 'schools').locations.push(school.school)
            })
        // Parks
        if (data['parks'])
            data['parks'].forEach((park) => {
                this.categorys.find(x => x.title == 'parks').locations.push(park.park)
            })
        // pets
        // if (data['pets'])
        //     data['pets'].forEach((pet) => {
        //         this.categorys.find(x => x.title == 'pets').locations.push(pet.pet)
        //     })
        // lidl
        // if (data['lidl'])
        //     data['lidl'].forEach((lidl) => {
        //         this.categorys.find(x => x.title == 'lidl').locations.push(lidl.lidl)
        //     })
        // cafes
        if (data['cafes'])
            data['cafes'].forEach((cafe) => {
                this.categorys.find(x => x.title == 'cafes').locations.push(cafe.cafe)
            })
        // allotments
        if (data['allotments'])
            data['allotments'].forEach((allotment) => {
                this.categorys.find(x => x.title == 'allotments').locations.push(allotment.allotment)
            })
        // bikes
        // if (data['bikes'])
        //     data['bikes'].forEach((bike) => {
        //         this.categorys.find(x => x.title == 'bikes').locations.push(bike.bike)
        //     })
        // parking
        if (data['parking'])
            data['parking'].forEach((parking) => {
                this.categorys.find(x => x.title == 'parking').locations.push(parking.parking)
            })
        // bus stops
        if (data['bus_stops'])
            data['bus_stops'].forEach((bus_stop) => {
                this.categorys.find(x => x.title == 'bus_stops').locations.push(bus_stop.bus_stop)
            })
        // station
        // if (data['station'])
        //     data['station'].forEach((station) => {
        //         this.categorys.find(x => x.title == 'station').locations.push(station.station)
        //     })
    },
    data: function () {
        return {
            zoom: 1,
            backgroundImage: '',
            transform: null,
            panner: null,
            windowWidth: 0,
            windowHeight: 0,
            counterZoom: 1,
            showFooter: false,
            location_selected: [],
            category_selected: '',
            options: {
                currentPage: 0
            },
            tabs: {
                description: false,
                opening_times: false,
                contact_details: false
            },
            tabSelected: null,
            categorys: [
                // {
                //     title: 'houses',
                //     iconColor: '#000',
                //     iconsSize: '38px',
                //     icon: require('./assets/SVG/houses.svg'),
                //     locations: [],
                // },
                {
                    title: 'schools',
                    iconsSize: '38px',
                    icon: require('./assets/SVG/schools.svg'),
                    iconColor: '#B77671',
                    locations: [],
                },
                {
                    title: 'parks',
                    icon: require('./assets/SVG/parks.svg'),
                    iconColor: '#FAC000',
                    iconsSize: '27px',
                    locations: [],
                },
                // {
                //     title: 'pets',
                //     icon: require('./assets/SVG/pets.svg'),
                //     iconColor: '#b77671',
                //     iconsSize: '38px',
                //     locations: [],
                // },
                // {
                //     title: 'lidl',
                //     icon: require('./assets/SVG/lidl.svg'),
                //     iconColor: '#b77671',
                //     iconsSize: '38px',
                //     locations: [],
                // },
                {
                    title: 'cafes',
                    icon: require('./assets/SVG/cafes.svg'),
                    iconColor: '#b77671',
                    iconsSize: '38px',
                    locations: [],
                },
                {
                    title: 'allotments',
                    icon: require('./assets/SVG/allotments.svg'),
                    iconColor: '#b77671',
                    iconsSize: '38px',
                    locations: [],
                },
                // {
                //     title: 'bikes',
                //     icon: require('./assets/SVG/bikes.svg'),
                //     iconColor: '#b77671',
                //     iconsSize: '38px',
                //     locations: [],
                // },
                {
                    title: 'parking',
                    icon: require('./assets/SVG/parking.svg'),
                    iconColor: '#b77671',
                    iconsSize: '38px',
                    locations: [],
                },
                {
                    title: 'bus',
                    icon: require('./assets/SVG/bus_stops.svg'),
                    iconColor: '#000',
                    iconsSize: '38px',
                    locations: [],
                },
                // {
                //     title: 'station',
                //     icon: require('./assets/SVG/station.svg'),
                //     iconColor: '#000',
                //     iconsSize: '38px',
                //     locations: [],
                // }
            ],
            iconClose: require('./assets/SVG/close.svg'),
            iconVArrow: require('./assets/SVG/v-arrow.svg'),
            iconLeaf: require('./assets/SVG/leaf.svg')
        }
    },
}
</script>

<style>
/* // Small devices (landscape phones, 576px and up) */
@media (min-width: 576px) {}

/* // Medium devices (tablets, 768px and up) */
@media (min-width: 768px) {}

/* // Large devices (desktops, 992px and up) */
@media (min-width: 992px) {}

/* // X-Large devices (large desktops, 1200px and up) */
@media (min-width: 1200px) {}

/* XX-Large devices (larger desktops, 1400px and up) */
@media (min-width: 1400px) {}

.bg-green {
    background-color: #00745F;
}

.text-green {
    color: #00745F;
}

.bg-pink {
    background-color: #FABDB8;
}

.btn-sq {
    width: 66px;
    height: 66px;
}

.map {
    width: 100%;
    background: rgb(255, 255, 255);
    background: -moz-linear-gradient(0deg, rgba(255, 255, 255, 1) 54%, rgba(227, 237, 249, 1) 100%);
    background: -webkit-linear-gradient(0deg, rgba(255, 255, 255, 1) 54%, rgba(227, 237, 249, 1) 100%);
    background: linear-gradient(0deg, rgba(255, 255, 255, 1) 54%, rgba(227, 237, 249, 1) 100%);
    filter: progid:DXImageTransform.Microsoft.gradient(startColorstr="#ffffff", endColorstr="#e3edf9", GradientType=1);
    position: relative;
    overflow: hidden;
    height: 400px;
}

@media (min-width: 768px) {
    .map {
        height: 600px;
    }
}

@media (min-width: 1920px) {
    .map {
        height: 1080px;
        width: 1920px;
    }
}

.mapBackground {
    height: 1080px;
    width: 1920px;
    background-size: cover;
    background-repeat: no-repeat;
    cursor: grab;
}

.map__footer {
    background-color: #fff;
    border: 1px #F6F6F6 solid;
    width: 100%;
    position: absolute;
    bottom: 0;
    left: 0;
    padding: 0;
    list-style: none;
    margin: 0 !important;
    display: flex;
    justify-content: space-around;
}

.map__footer__item {
    height: 75px;
    text-align: center;
    color: #00745F;
    padding-top: 15px;
    width: 100px;
    cursor: pointer;
}

.map__footer__item:hover svg {
    border-color: #043C2C;
    fill: #043C2C;
}

.map__footer__item:hover svg * {
    fill: #043C2C;
}

.map__footer__item:hover h3 {
    color: #043C2C;
}

.map__footer h3 {
    font-size: 20px !important;
    margin-top: 5px;
    transition-duration: 0.2s;
}

.map__footer__item svg {
    height: 35px;
    width: 35px;
    border: 1.5px solid #00745F;
    margin: auto;
    padding: 5px;
    border-radius: 100%;
    transition-duration: 0.2s;
}

.map__footer p {
    font-size: 11px !important;
}

@media (min-width: 1920px) {

    .map__footer {
        background-color: #00745F;
        height: 168px;
        padding-left: 200px;
        justify-content: flex-start;
        border: none;
    }

    .map__footer__item svg {
        height: 70px;
        width: 70px;
        margin: auto;
        border-color: #fff;
        border-width: 3px;
        padding: 10px;
    }

    .map__footer__item {
        width: 170px;
        color: #fff;
        padding-top: 2.5em
    }

    .inactiveIcon * {
        fill: #fff;
        color: #fff;
    }
}

@media (max-width: 1920px) {
    .map__footer__item.activeIcon {
        border-top: 3px #043C2C solid;
        margin-top: -3px;
    }

    .map__footer p {
        font-weight: bolder !important;
        text-transform: capitalize;
        margin-top: -5px;
    }

    .inactiveIcon * {
        fill: #043C2C;
        color: #043C2C;
    }
}

.fillPink {
    fill: #FABDB8 !important;
    color: #FABDB8 !important;
}

.fillGreen {
    fill: #00745F !important;
    color: #00745F !important;
}

.activeIcon * {
    color: #FABDB8 !important;
}

.activeIcon svg {
    background-color: #B77670;
    border-color: #043C2C;
}

button {
    cursor: pointer;
}

.filterButton {
    width: auto;
    position: absolute;
    bottom: 50px;
    left: 50px;
    font-size: 30px;
    display: flex;
    align-items: center;
    cursor: pointer;
}

.filterButton h3 {
    margin: 0;
    padding-bottom: 0;
    padding-right: 30px;
}

.locations {
    position: relative;
    height: 100%;
    width: 100%;
}

.locations__marker {
    position: absolute;
    max-width: 170px;
    text-align: center;
    cursor: pointer;
}

.locations__marker:hover {
    transition-duration: 0.2s;
    transform: scale(1.1);
}

.locations__marker__icon {
    border-radius: 100px;
    position: relative;
    padding: 8px;
    margin: auto;
    height: 48px;
    width: 48px;
    border: 1px #fff solid;
}

.locations__marker__icon svg {
    position: absolute;
    width: 30px;
    object-fit: contain;
    left: 0;
    right: 0;
    margin: auto;
    top: 0;
    bottom: 0;
}

.locations__marker__title {
    color: #2c2e2c;
    font-size: 26px;
    margin-top: 13px;
}

.infoWindowSmall {
    border: 1px #F6F6F6 solid;
    width: calc(100%-1em);
    position: absolute;
    bottom: 100px;
    box-shadow: 0 1px 1px 0 rgba(187, 185, 185, 0.50);
    min-height: 111px;
    left: 1em;
    right: 1em;
    background-color: #fff;
    border-radius: 10px;
    overflow: hidden;
    display: flex;
}

.infoWindowSmall__inner {
    padding: 1em 3em 1em 1em;
    display: inline-block;
}

.infoWindowSmall__inner p {
    line-height: 1 !important;
}

.infoWindowSmall__inner p:last-of-type() {
    margin-bottom: 0 !important;
}

.infoWindow {
    position: absolute;
    width: 390px;
    top: 139px;
    min-height: 400px;
    background-color: #fff;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: rgba(0, 0, 0, 0.12) 0px 1px 3px, rgba(0, 0, 0, 0.24) 0px 3px 2px;
    transform: translateX(-25%);
}

.infoWindow__slider {
    height: 285px;
    position: relative;
    background-color: #000;
}

.arrows {
    position: absolute;
    top: 50%;
    width: 22px;
    height: 7px;
    z-index: 10;
    cursor: pointer;
}

.swiper-container-horizontal>*>.slider-pagination-bullet {
    background: none;
    border-radius: 100%;
    display: inline-block;
    height: 11px !important;
    opacity: 1 !important;
    width: 11px !important;
    cursor: pointer;
    margin: 0 5px;
}

.slider-pagination-bullet-active {
    background-color: #fff !important;
}

.arrows * {
    fill: #fff;
}

.arrows.left {
    left: 20px;
    transform: rotate(90deg);
}

.arrows.right {
    right: 20px;
    transform: rotate(-90deg);
}

.infoWindow__slider img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    opacity: 0.8;
}

.infoWindow__inner__item {
    border-bottom: 3px #FABDB8 solid;
    display: block;
    padding: 17px 0 15px 0;
    cursor: pointer;
    position: relative;
}

.infoWindow__inner__item__arrow {
    position: absolute;
    top: 18px;
    right: 0;
    height: 17px;
    width: 27px;
    padding: 0;
}

.infoWindow__inner__item__content {
    /* padding-top: 17px; */
    display: block;
}

.infoWindow__inner__item h3,
.infoWindow__inner__item p {
    padding: 0;
    margin: 0;
}

.infoWindow__inner__item p {
    line-height: 1.2;
}

.infoWindow__close {
    position: absolute;
    top: 20px;
    z-index: 10;
    right: 20px;
    width: 20px;
    cursor: pointer;
    height: 20px;
}

.darkBtn * {

    stroke: black;

}

.slider-pagination-bullet {
    background: transparent;
    border: 3px solid #fff;
}

.infoWindow__title {
    position: absolute;
    color: #fff;
    top: 20px;
    left: 20px;
    max-width: 230px;
    z-index: 20;
}

.infoWindow__inner {
    padding: 0 20px 10px 20px;
    color: #005A44;
}

@media (max-width: 800px) {

    .infoWindow {
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        top: unset;
        min-height: 400px;
        background-color: #fff;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: rgba(0, 0, 0, 0.12) 0px 1px 3px, rgba(0, 0, 0, 0.24) 0px 3px 2px;
        transform: translateX(0);
    }

}

.map__title {
    position: absolute;
    top: 50px;
    z-index: 20;
    left: 50px;
}

.map__title__leaf svg {
    width: 124px;
    height: 118px
}

@media (max-width: 1920px) {
    .map__title {
        left: 5px;
        top: 5px;
    }

    .map__title h1 {
        display: none !important;
    }

    .map__title__leaf svg {
        width: 60px;
        height: 70px
    }
}

.bigLeafActive * {
    fill: #FABDB8;
}

.map__title h1 {
    margin-bottom: 0;
}

.v-enter-active,
.v-leave-active {
    transition: opacity 0.5s ease;
}

.bounce-enter-active {
    animation: bounce-in 0.5s;
}

.bounce-leave-active {
    animation: bounce-in 0.5s reverse;
}

.zoomer {
    position: absolute;
    top: 10px;
    right: 10px;
    height: 57px;
    z-index: 4;
    width: 30px;
    background: #FFF4F3;
    box-shadow: 0 1px 1px 0 rgba(153, 153, 153, 0.50);
    border-radius: 5.2px;
}

.zoomer button {
    display: block;
    height: 28.5px;
    font-size: 1.4em;
    width: 30px;
}

@keyframes bounce-in {
    0% {
        transform: scale(0.8);
        opacity: 0;
    }

    50% {
        transform: scale(1.25);
        opacity: 0.5;
    }

    100% {
        transform: scale(1);
        opacity: 1;
    }
}
</style>
