<template>
<div class="map" id="vue-frontend-app" :style="{'background-image': 'url(' + require('./assets/map-back.jpg') + ')'}">

    <!-- MAP TITLE -->
    <div class="map__title text-green">
        <div style="width: 124px; height:118px" :class="showFooter ? 'fillPink' : 'fillGreen'">
            <span v-html="iconLeaf"></span>
        </div>
        <fade-transition>
            <span v-show="!showFooter">
                <h1>THIS IS</h1>
                <h1>YOUR</h1>
                <h1>LINMERE</h1>
            </span>
        </fade-transition>

    </div>
    <!-- MAP TITLE END -->

    <!-- MAP ELEMENTS -->

    <template v-for="(cat) in categorys">
        <zoom-center-transition :delay="100">
            <div v-show="!category_selected || category_selected == cat" v-for="(location) in cat.locations" :key="location.title" class="locations__marker" :style="{top:location.y+'px', left:+location.x+'px'}" @click="popUp(location)">
                <div class="locations__marker__icon" :style="{backgroundColor:cat.iconColor}" v-html="cat.icon"></div>
                <h4 class="locations__marker__title">{{location.title}}</h4>
            </div>
        </zoom-center-transition>
    </template>

    <!-- MAP ELEMENTS END -->

    <!--INFO WINDOW -->
    <fade-transition>
        <div v-if="Object.keys(location_selected).length" class="infoWindow" :style="{left: location_selected.x+'px'}">
            <button class="infoWindow__close" @click="popUp()">
                <div v-html="iconClose"></div>
            </button>
            <h3 class="infoWindow__title">{{location_selected.longTitle}}</h3>

            <slider class="infoWindow__slider" ref="slider" :options="options">
                <slideritem v-for="(image,i) in location_selected.images" :key="i">
                    <img :src="image.sizes.medium">
                </slideritem>
                <div slot="loading">loading...</div>
            </slider>

            <div class="infoWindow__inner">
                <span v-for="(tab, key, i) in tabs" :key="i" class="infoWindow__inner__item" @click="selectTab(key)">
                    <div class="infoWindow__inner__item__arrow" v-html="iconVArrow" :style="tab ? 'transform: rotate(180deg)':null"></div>
                    <h3 class="infoWindow__inner__item__title">
                        {{key.replaceAll('_',' ')}}
                    </h3>
                    <collapse-transition>
                        <div v-if="tab" class="infoWindow__inner__item__content">
                            <p v-html="location_selected.description">
                            </p>
                        </div>
                    </collapse-transition>
                </span>
            </div>
        </div>
    </fade-transition>
    <!-- INFO WINDOW END -->

    <!-- FOOTER -->
    <slide-y-down-transition>
        <div v-show="showFooter" class="map__footer">
            <div v-for="(cat, i) in categorys" :key="i" class="map__footer__item " :class="cat == category_selected ? 'activeIcon' : 'inacitveIcon' " @click="category_selected = cat">
                <div v-html="cat.icon"></div>
                <h3 :style="cat == category_selected ? 'opacity: 1' : 'opacity: 0'">{{cat.title.replace('_',' ')}}</h3>
            </div>
        </div>
    </slide-y-down-transition>
    <slide-x-left-transition>
        <div class="filterButton" @click="showFooter = !showFooter">

            <h3 class="text-green" v-show="!showFooter">FILTER</h3>

            <button v-html="iconVArrow" :class="showFooter ? 'bg-pink' :'bg-green'" style="padding: 18px" class="btn-sq inactiveIcon" :style="!showFooter ? 'transform: rotate(270deg)' : 'transform: rotate(90deg)'"></button>
        </div>
    </slide-x-left-transition>
    <!-- FOOTER END -->
</div>
</template>

<script>
import { FadeTransition, SlideXLeftTransition, SlideYDownTransition, CollapseTransition, SlideYUpTransition, ZoomCenterTransition } from 'vue2-transitions'
import { slider, slideritem } from 'vue-concise-slider'
import axios from 'axios';

export default {
    name: 'App',
    methods: {
        popUp(location) {
            location ? this.location_selected = location : this.location_selected = []
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
        CollapseTransition,
        ZoomCenterTransition,
    },
    created() {

        axios.get("/wp-json/wp/v2/pages")
            .then(response => {
                const page = response.data.find(page => page.slug == 'kiosk')
                // Schools
                page.acf['schools'].forEach((school) => {
                    this.categorys.find(x => x.title == 'schools').locations = Object.values(school)
                })

            })
            .catch(e => {
                console.log(e)
            })
    },

    data: function () {
        return {
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
            categorys: [{
                    title: 'houses',
                    iconColor: '#000',
                    icon: require('./assets/SVG/houses.svg'),
                    locations: [
                        //     {
                        //     title: 'gfdg',
                        //     longTitle: 'gfdgd',
                        //     description: '',
                        //     openingTimes: '',
                        //     contactDetails: '',
                        //     images: [],
                        //     x: 700,
                        //     y: 200,
                        // }
                    ],
                },
                {
                    title: 'schools',
                    icon: require('./assets/SVG/schools.svg'),
                    iconColor: '#b77671',
                    locations: [
                        //     {
                        //     title: 'Tithe farm school',
                        //     longTitle: 'Tithe farm recreation school',
                        //     description: 'Id nulla adipisicing dolor elit fugiat officia cillum veniam tempor fugiat.',
                        //     openingTimes: 'Exercitation reprehenderit sit adipisicing et veniam.',
                        //     contactDetails: 'Incididunt id pariatur labore pariatur id irure mollit voluptate aliqua.',
                        //     images: ['https://i.cbc.ca/1.5767437.1639580237!/fileImage/httpImage/image.jpg_gen/derivatives/16x9_780/schools-prepare-for-a-january-shutdown.jpg', 'https://www.irishtimes.com/polopoly_fs/1.4073214.1572967755!/image/image.jpg_gen/derivatives/box_620_330/image.jpg'],
                        //     x: 517,
                        //     y: 712,
                        // }
                    ],
                },
                {
                    title: 'parks',
                    icon: require('./assets/SVG/parks.svg'),
                },
                {
                    title: 'pets',
                    icon: require('./assets/SVG/pets.svg'),
                },
                {
                    title: 'lidl',
                    icon: require('./assets/SVG/lidl.svg'),
                },
                {
                    title: 'cafes',
                    icon: require('./assets/SVG/cafes.svg'),
                },
                {
                    title: 'alotments',
                    icon: require('./assets/SVG/alotments.svg'),
                },
                {
                    title: 'bikes',
                    icon: require('./assets/SVG/bikes.svg'),
                },
                {
                    title: 'parking',
                    icon: require('./assets/SVG/parking.svg'),
                },
                {
                    title: 'bus stops',
                    icon: require('./assets/SVG/bus_stops.svg'),
                },
                {
                    title: 'station',
                    icon: require('./assets/SVG/station.svg'),
                }
            ],
            iconClose: require('./assets/SVG/close.svg'),
            iconVArrow: require('./assets/SVG/v-arrow.svg'),
            iconLeaf: require('./assets/SVG/leaf.svg')
        }
    },
}
</script>

<style>
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
    height: 1080px;
    width: 1920px;
    border: #000;
    background-color: #bed8a7;
    position: relative;
    background-size: cover;
    background-repeat: no-repeat;
}

.map__footer {
    background-color: #00745F;
    width: 100%;
    height: 168px;
    position: absolute;
    bottom: 0;
    left: 0;
    padding: 0;
    list-style: none;
    margin: 0 !important;
    display: flex;
    padding-left: 200px;
}

.map__footer__item {
    padding-top: 10px;
    height: auto;
    text-align: center;
    color: #fff;
    padding-top: 2.5em;
    width: 149px;
    cursor: pointer;
    transition-duration: 0.5;
}

.map__footer__item:hover {
    transform: scale(1.1);
}

.map__footer h3 {
    font-size: 20px !important;
    margin-top: 5px;
}

.map__footer__item svg {
    height: 70px;
    width: 70px;
    margin: auto;
}

.fillPink {
    fill: #FABDB8 !important;
    color: #FABDB8 !important;
}

.fillGreen {
    fill: #00745F !important;
    color: #00745F !important;
}

.inactiveIcon * {
    fill: #fff !important;
    color: #fff !important;
}

.activeIcon * {
    fill: #FABDB8 !important;
    color: #FABDB8 !important;
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
    transition-duration: 0.5;
}

.locations__marker:hover {
    transform: scale(1.1);
}

.locations__marker__icon {
    border-radius: 100px;
    padding: 8px;
    margin: auto;
    height: 48px;
    width: 48px;
    border: 1px #fff solid;
}

.locations__marker__title {
    color: #2c2e2c;
    font-size: 26px;
    margin-top: 13px;
}

.infoWindow {
    position: absolute;
    width: 390px;
    top: 139px;
    min-height: 400px;
    background-color: #fff;
    border-radius: 10px;
    overflow: hidden;
    transform: translateX(-25%);
}

.infoWindow__slider {
    height: 285px;
    position: relative;
    background-color: #00745F;
}

.infoWindow__slider img {
    width: 100%;
    height: 100%;
    object-fit: cover;
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
    padding-top: 17px;
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
    width: 30px;
    cursor: pointer;
    height: 30px;
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

.map__title {
    position: absolute;
    top: 50px;
    left: 50px;
}

.map__title h1 {
    margin-bottom: 0;
}
</style>
