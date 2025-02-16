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
                <div>
                    <img
                        :src="data"
                        alt=""
                        loading="lazy"
                        class="img-fluid object-cover rounded-3"
                    />
                </div>
            </SwiperSlide>
        </Swiper>
    </div>

    <div class="tab-slider mt-3">
        <!-- Thumbnail Swiper -->
        <Swiper
            class="swiper-thumb overflow-hidden"
            :modules="[Controller]"
            :slidesPerView="4"
            :spaceBetween="10"
            :watchSlidesProgress="true"
            :grabCursor="true"
            :slideToClickedSlide="true"
            @swiper="setThumbSwiper"
            @click="updateMainSwiper"
        >
            <SwiperSlide v-for="(data, index) in props.attachments" :key="index">
                <div class="thumb-wrapper p-1 rounded-3">
                    <img
                        :src="data"
                        alt=""
                        loading="lazy"
                        class="img-fluid object-cover rounded-3"
                        @click="updateMainSwiper(index)"
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
const props = defineProps(["attachments"]);

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
    if (mainSwiper.value) {
        nextTick(() => {
            mainSwiper.value.slideTo(index); // Move the main Swiper to the clicked index
        });
    }
};
</script>

<style scoped>
.tab-slider .swiper-content img,
.tab-slider .swiper-thumb img {
    cursor: pointer;
}

.tab-slider .thumb-wrapper {
    border: 1px solid #ddd;
    transition: border-color 0.3s;
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