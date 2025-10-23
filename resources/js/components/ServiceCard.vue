<template>
   <div class="service-box-card bg-light rounded-3 mb-5">
  <div class="iq-image position-relative">
      <span v-if="visit_type == 'ONLINE'" class="online-service"></span>
     <a :href="`${baseUrl}/service-detail/${service_id}`"  @click="storeRecentlyViewed" class="service-img">
        <img :src="image ? image : baseUrl+'/images/default.png'" alt="service"
        class="service-img w-100 object-cover img-fluid rounded-3">
     </a>

      <div v-if="user_id !== null">
         <div v-if="favourite == false">
               <form @submit.prevent="saveFavourite">
                  <input type="hidden" name="service_id"  :value="service_id">
                  <input type="hidden" name="user_id"  :value="user_id">
                  <button type="submit" class="btn-link serv-whishlist text-primary">
                     <svg width="12" height="13" viewBox="0 0 12 13" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M1.43593 6.29916C0.899433 4.62416 1.52643 2.70966 3.28493 2.14316C4.20993 1.84466 5.23093 2.02066 5.99993 2.59916C6.72743 2.03666 7.78593 1.84666 8.70993 2.14316C10.4684 2.70966 11.0994 4.62416 10.5634 6.29916C9.72843 8.95416 5.99993 10.9992 5.99993 10.9992C5.99993 10.9992 2.29893 8.98516 1.43593 6.29916Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M8 3.84998C8.535 4.02298 8.913 4.50048 8.9585 5.06098" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                     </svg>
                  </button>
               </form>
         </div>
         <div v-else>
            <form @submit.prevent="deleteFavourite">
               <input type="hidden" name="service_id"  :value="service_id">
               <input type="hidden" name="user_id"  :value="user_id">
               <button type="submit" class="btn-link serv-whishlist text-primary">
                  <svg width="12" height="13" viewBox="0 0 12 13" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                     <path fill-rule="evenodd" clip-rule="evenodd" d="M1.43593 6.29916C0.899433 4.62416 1.52643 2.70966 3.28493 2.14316C4.20993 1.84466 5.23093 2.02066 5.99993 2.59916C6.72743 2.03666 7.78593 1.84666 8.70993 2.14316C10.4684 2.70966 11.0994 4.62416 10.5634 6.29916C9.72843 8.95416 5.99993 10.9992 5.99993 10.9992C5.99993 10.9992 2.29893 8.98516 1.43593 6.29916Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                     <path d="M8 3.84998C8.535 4.02298 8.913 4.50048 8.9585 5.06098" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                  </svg>
               </button>
            </form>
         </div>
      </div>
      <div v-else>
         <form @submit.prevent="redirectToLogin">
            <button type="submit" class="btn-link serv-whishlist text-primary">
               <svg width="12" height="13" viewBox="0 0 12 13" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path fill-rule="evenodd" clip-rule="evenodd" d="M1.43593 6.29916C0.899433 4.62416 1.52643 2.70966 3.28493 2.14316C4.20993 1.84466 5.23093 2.02066 5.99993 2.59916C6.72743 2.03666 7.78593 1.84666 8.70993 2.14316C10.4684 2.70966 11.0994 4.62416 10.5634 6.29916C9.72843 8.95416 5.99993 10.9992 5.99993 10.9992C5.99993 10.9992 2.29893 8.98516 1.43593 6.29916Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                  <path d="M8 3.84998C8.535 4.02298 8.913 4.50048 8.9585 5.06098" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
               </svg>
            </button>
         </form>
      </div>

  </div>
  <a :href="`${baseUrl}/service-detail/${service_id}`" class="service-heading mt-4 d-block p-0" @click="storeRecentlyViewed">
    <h5 class="service-title font-size-18 line-count-2">{{title }}</h5>
  </a>
  <div class="meta-row d-flex align-items-center justify-content-between mt-2">
    <div class="meta-left d-flex align-items-center gap-2">
      <span v-if="visit_type" class="type-badge">{{ typeLabel }}</span>
      <span v-if="duration && duration !== '00:00'" class="duration-text">{{ formattedDuration(duration) }}</span>
    </div>
    <div class="meta-right d-flex align-items-center gap-2">
      <span v-if="discount && discount > 0" class="discount-badge">-{{ discount }}%</span>
      <span class="price-chip" :class="{ 'is-free': !(price>0) }">
        <span v-if="price>0">{{ formatCurrencyVue(price) }}</span>
        <span v-else>Free</span>
      </span>
    </div>
  </div>
  <div
     class="mt-3">
     <div class="d-flex align-items-center gap-2">
        <img :src="userImage" alt="service" class="img-fluid rounded-3 object-cover avatar-24">
        <a :href="`${baseUrl}/provider-detail/${provider_id}`"><span class="font-size-14 service-user-name">{{ userName }}</span></a>
     </div>
           <div class="d-flex align-items-center gap-1 f-none mt-2">
       <rating-component :readonly = true :showrating ="false" :ratingvalue="props.reviewNo" />              

        <h6 class="font-size-14">{{reviewNo }}
    <a :href="`${baseUrl}/rating-all?service_id=${service_id}`"><span v-if="reviewCount>1" class="text-body ms-1">({{ reviewCount }} {{ $t('messages.reviews') }})</span><span v-else class="text-body ms-1">({{ reviewCount }} {{ $t('messages.review') }})</span>
    </a>
   </h6>
   <span class="ms-3 d-inline-flex align-items-center" title="Views">
     <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns=" "><path d="M12 5c-7.633 0-10 7-10 7s2.367 7 10 7 10-7 10-7-2.367-7-10-7Zm0 12a5 5 0 1 1 0-10 5 5 0 0 1 0 10Zm0-8a3 3 0 1 0 .002 6.002A3 3 0 0 0 12 9Z" fill="currentColor"/></svg>
     <span class="ms-1">{{ Number(props.totalViews || 0) }}</span>
   </span>
      </div>
  </div>
</div>
</template>

<script setup>
import { ref ,onMounted, computed } from 'vue';
import axios from 'axios';
import { SAVE_FAVOURITE_API, DELETE_FAVOURITE_API} from '../data/api';
import Swal from 'sweetalert2';
import { extendWith } from 'lodash';

const props = defineProps({
    image: {type:String ,default:''},
    userImage: {type:String ,default:''},
    userName: {type:String ,default:''},
    reviewNo: {type:Number ,default:0},
        reviewCount: {type:Number ,default:0},
    title: {type:String ,default:''},
    price: {type:Number ,default:0},
    duration: {type:String ,default:''},
    service_id : {type: Number, default: 0},
    provider_id : {type: Number, default: 0},
    user_id : {type: Number, default: 0},
    favourite : {type: Boolean, default: ''},
    visit_type : {type: String, default: ''},
    discount : {type: Number, default: 0},
    totalViews : {type: Number, default: 0},
 })

const baseUrl = document.querySelector('meta[name="baseUrl"]').getAttribute('content');
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
const saveFavourite = async(values) => {
   values.service_id = props.service_id;
   values.user_id = props.user_id;

   if(props.user_id !== ""){
      try {
         const response = await fetch(SAVE_FAVOURITE_API, {
            method: 'POST',
            headers: {
               'Content-Type': 'application/json',
               'X-CSRF-TOKEN': csrfToken,
            },
            body:JSON.stringify(values),
         });

         if(response.ok) {
            const responseData = await response.json();
            Swal.fire({
            title: 'Done',
            text: responseData.message,
            icon: 'success',
            iconColor: '#5F60B9'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.reload();
                }
            })
        }
      } catch (error) {
         console.error(error);
      }
   }
   else{
      window.location.href = baseUrl + '/login-page';
   }
};
const deleteFavourite = async(values) => {
   values.service_id = props.service_id;
   values.user_id = props.user_id;
   try {
      const response = await fetch(DELETE_FAVOURITE_API, {
         method: 'POST',
         headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
         },
         body:JSON.stringify(values),
      });

      if(response.ok) {
         const responseData = await response.json();
         Swal.fire({
         title: 'Done',
         text: responseData.message,
         icon: 'success',
         iconColor: '#5F60B9'
         }).then((result) => {
               if (result.isConfirmed) {
                  window.location.reload();
               }
         })
      }
   } catch (error) {
      console.error(error);
   }
};
const redirectToLogin = () => {
   window.location.href = baseUrl + '/login-page';
}

const storeRecentlyViewed = async () => {
   try {
      const response = await axios.post(`${baseUrl}/save-recently-viewed/${props.service_id}`);
      if (response.data.success) {

         return response.data.success;
      } else {

         console.error(response.data.success);
      }
   } catch (error) {
      console.error('Error storing service ID in session for recently viewed', error);
   }
};

const formatCurrencyVue = (value) => {

   if(window.currencyFormat !== undefined) {
   return window.currencyFormat(value)
   }
   return value
}

import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const formattedDuration = () => {
    if (props.duration) {
        const durationParts = props.duration.split(':');
        const hours = parseInt(durationParts[0], 10);
        const minutes = parseInt(durationParts[1], 10);

        if (hours > 0) {
            return `${hours} ${t('landingpage.hrs')} ${minutes} ${t('landingpage.min')}`;
        } else {
            return `${minutes} ${t('landingpage.min')}`;
        }
    } else {
        return ''; // or any default value you want to show if duration is not provided
    }
}

const typeLabel = computed(() => {
    if (!props.visit_type) return '';
    return props.visit_type === 'ONLINE' ? 'Online' : 'On-site';
});
</script>
<style scoped>
.service-box-card { transition: box-shadow .2s ease, transform .2s ease; border: 1px solid #E8E9EC; }
.service-box-card:hover { box-shadow: 0 8px 24px rgba(16,24,40,.08); transform: translateY(-1px); }
.iq-image { overflow: hidden; }
.service-img { display: block; }
.serv-whishlist { position: absolute; top: 10px; right: 10px; background: #fff; width: 32px; height: 32px; border-radius: 999px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(16,24,40,.12); }
.online-service { position: absolute; left: 10px; top: 10px; width: 10px; height: 10px; background: #22c55e; border-radius: 50%; box-shadow: 0 0 0 3px rgba(34,197,94,.2); }
.type-badge { background: #F1F5F9; color: #334155; padding: 4px 8px; border-radius: 999px; font-size: 12px; font-weight: 600; }
.duration-text { color: #6B7280; font-size: 12px; }
.price-chip { background: #EEF2FF; color: #4338CA; padding: 6px 10px; border-radius: 10px; font-weight: 700; font-size: 14px; }
.price-chip.is-free { background: #ECFDF5; color: #065F46; }
.discount-badge { background: #FEF2F2; color: #B91C1C; padding: 4px 6px; border-radius: 8px; font-size: 12px; font-weight: 700; }
.service-title { margin-bottom: 0; }
</style>
