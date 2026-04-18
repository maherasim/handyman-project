<template>
    <div class="tab-slider">
        <!-- Main Swiper -->
        <Swiper
            class="swiper-content position-relative overflow-hidden"
            :modules="[Controller]"
            :slidesPerView="1"
            :grabCursor="true"
            @swiper="setMainSwiper"
        >
            <SwiperSlide v-for="(data, index) in props.attachments" :key="index">
                <div
                    class="main-slide-frame rounded-3"
                    :class="props.mainFit === 'contain' ? 'main-slide-frame--contain' : 'main-slide-frame--cover'"
                >
                    <img
                        :src="data"
                        alt=""
                        loading="lazy"
                        class="main-slide-img"
                    />
                </div>
            </SwiperSlide>
        </Swiper>
    </div>

    <div class="tab-slider mt-3" v-show="props.attachments.length > 1">
        <!-- Thumbnail Swiper -->
        <Swiper
            class="swiper-thumb overflow-hidden"
            :modules="[Controller]"
            :slidesPerView="4"
            :spaceBetween="10"
            :watchSlidesProgress="true"
            :grabCursor="true"
            :allowTouchMove="false"
            @swiper="setThumbSwiper"
        >
            <SwiperSlide v-for="(data, index) in props.attachments" :key="index">
                <div class="thumb-wrapper p-1 rounded-3" @click="updateMainSwiper(index)">
                    <img
                        :src="data"
                        alt=""
                        loading="lazy"
                        class="thumb-slide-img"
                    />
                </div>
            </SwiperSlide>
        </Swiper>
    </div>
</template>

<script setup>
import { ref, nextTick } from "vue";
import { Swiper, SwiperSlide } from "swiper/vue";
import { Controller } from "swiper";

// Define props for the component
const props = defineProps({
    attachments: { type: Array, default: () => [] },
    mainFit: { type: String, default: 'cover' } // 'cover' | 'contain'
});

// Main Swiper and Thumbnail Swiper references
const mainSwiper = ref(null);
const thumbSwiper = ref(null);

// Functions to set the Swiper instances
const setMainSwiper = (swiper) => {
    mainSwiper.value = swiper;
};
const setThumbSwiper = (swiper) => {
    thumbSwiper.value = swiper;
};

// Function to update main Swiper when a thumbnail is clicked
const updateMainSwiper = (index) => {
    if (mainSwiper.value != null && typeof index === 'number') {
        nextTick(() => {
            mainSwiper.value.slideTo(index);
        });
    }
};
</script>

<style scoped>
/* Main gallery: dynamic height so images define their own space without blank areas */
.main-slide-frame {
    position: relative;
    width: 100%;
    overflow: hidden;
    background: transparent;
    border-radius: 8px;
    display: flex;
    justify-content: center;
    align-items: center;
}

.main-slide-frame--cover .main-slide-img {
    width: 100%;
    height: auto;
    max-height: 600px;
    object-fit: cover;
    display: block;
}

.main-slide-frame--contain .main-slide-img {
    width: 100%;
    height: auto;
    max-height: 600px;
    object-fit: contain;
    display: block;
}

/* Thumbnails: uniform tiles (object-fit needs explicit box) */
.thumb-wrapper {
    border: 1px solid #ddd;
    transition: border-color 0.3s;
    cursor: pointer;
    height: 76px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
}

.thumb-slide-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.tab-slider .thumb-wrapper:hover {
    border-color: #000;
}

.tab-slider .swiper-thumb {
    max-width: 100%;
}

.tab-slider .swiper-content {
    max-width: 100%;
}

</style>