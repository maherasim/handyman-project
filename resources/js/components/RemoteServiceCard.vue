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
   <ul class="list-inline p-0 mt-1 mb-0 price-content">
    <li class="text-primary fw-500 d-inline-block position-relative" style="font-size: 16px">
        <span v-if="price>0">{{ formatCurrencyVue(price) }} <span v-if="discount && discount > 0"> ({{ discount }}% off)</span></span>
        <span v-else>Free</span>
    </li>
    <li v-if="duration && duration !== '00:00'" class="d-inline-block fw-500 position-relative service-price">({{ formattedDuration(duration) }})</li>
</ul>
  <div
     class="mt-3">
     <div class="d-flex align-items-center gap-2">
        <img :src="userImage" alt="service" class="img-fluid rounded-3 object-cover avatar-24">
        <a :href="`${baseUrl}/provider-detail/${provider_id}`"><span class="font-size-14 service-user-name">{{ userName }}</span></a>
     </div>
           <!-- Statistics Section - Optimized Layout -->
      <div class="stats-section mt-3">
         <div class="row g-2 text-center">
            <!-- Reviews Section -->
            <div class="col-4">
               <div class="stats-item">
                  <div class="d-flex align-items-center justify-content-center mb-1">
                     <rating-component :readonly="true" :showrating="false" :ratingvalue="props.reviewNo" />
                     <span class="stats-value ms-1">{{ props.reviewNo }}</span>
                  </div>
                  <div class="stats-label">
                     <a :href="`${baseUrl}/rating-all?service_id=${service_id}`" class="text-decoration-none text-muted">
                        ({{ props.reviewCount }} {{ props.reviewCount > 1 ? $t('messages.reviews') : $t('messages.review') }})
                     </a>
                  </div>
               </div>
            </div>
            
            <!-- Bookings Section -->
            <div class="col-4">
               <div class="stats-item">
                  <div class="d-flex align-items-center justify-content-center mb-1">
                     <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns=" " class="stats-icon text-success">
                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                     </svg>
                     <span class="stats-value">{{ props.totalBookings || 0 }}</span>
                  </div>
                  <div class="stats-label">Bookings</div>
               </div>
            </div>
            
            <!-- Views Section -->
            <div class="col-4">
               <div class="stats-item">
                  <div class="d-flex align-items-center justify-content-center mb-1">
                     <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns=" " class="stats-icon text-info">
                        <path d="M12 5c-7.633 0-10 7-10 7s2.367 7 10 7 10-7 10-7-2.367-7-10-7Zm0 12a5 5 0 1 1 0-10 5 5 0 0 1 0 10Zm0-8a3 3 0 1 0 .002 6.002A3 3 0 0 0 12 9Z" fill="currentColor"/>
                     </svg>
                     <span class="stats-value">{{ Number(props.totalViews || 0) }}</span>
                  </div>
                  <div class="stats-label">Views</div>
               </div>
            </div>
         </div>
      </div>
  </div>
</div>
</template>

<style scoped>
/* Statistics section improvements */
.stats-section {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 12px 8px;
    margin: 12px 0;
}

.stats-item {
    text-align: center;
    padding: 4px;
}

.stats-icon {
    width: 16px;
    height: 16px;
    margin-right: 4px;
}

.stats-value {
    font-weight: 600;
    font-size: 14px;
    color: #2c3e50;
}

.stats-label {
    font-size: 11px;
    color: #6c757d;
    margin-top: 2px;
}

/* Card polish */
.service-box-card { 
    border: 1px solid #eef0f2; 
    transition: box-shadow .2s ease, transform .2s ease; 
    background: #fff; 
    padding: 15px;
    border-radius: 12px;
}
.service-box-card:hover { 
    box-shadow: 0 10px 24px rgba(18,38,63,.08); 
    transform: translateY(-2px); 
}
</style>

<script setup>
import { ref ,onMounted} from 'vue';
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
            iconColor: '#3333ff'
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
         iconColor: '#3333ff'
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
</script>
