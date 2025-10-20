<template>
  <section ref="servicesection">

    <div class="row align-items-center">
      <div class="col-lg-12">
        <div class="row gx-2 align-items-end">
          <div class="col-lg-2 col-md-4 col-sm-6">
            <select :key="'cat-'+(category_data?.length || 0)" ref="categoryDropdownRef" id="categoryDropdown" v-model="selectedCategory"
              class="me-5 form-select select2" :disabled="isEmpty">
              <option value="">{{ $t('landingpage.all_categories') }}</option>
              <option v-for="category in category_data" :key="category.id" :value="category.id">{{ category.name }}
              </option>
            </select>

          </div>
          <div class="col-lg-2 col-md-4 col-sm-6">
            <select :key="'subcat-'+(sub_category_data?.length || 0)" ref="subCategoryDropdownRef" id="subCategoryDropdown" v-model="selectedSubCategory"
              class="me-5 form-select select2" :disabled="isEmpty">
              <option value="">{{ $t('Sub Categories') }}</option>
              <option v-for="category in sub_category_data" :key="category.id" :value="category.id">{{ category.name }}
              </option>
            </select>

          </div>
          
          <div class="col-lg-2 col-md-4 col-sm-6 mt-sm-0 mt-3">
            <select :key="'country-'+(countries?.length || 0)" ref="countryDropdownRef" id="countryDropdown" v-model="selectedCountry"
              class="me-5 form-select select2">
              <option value="">{{ $t('Filter by Country') }}</option>
              <option v-for="country in countries" :key="country.id" :value="country.id">{{ country.name }}</option>
            </select>
          </div>


          <div class="col-lg-2 col-md-4 col-sm-6 mt-sm-0 mt-3">
            <select :key="'city-'+(cities?.length || 0)" ref="cityDropdownRef" id="cityDropdown" v-model="selectedCity"
              class="me-5 form-select select2" :disabled="isEmpty">
              <option value="">{{ $t('Filter by City') }}</option>
              <option v-for="city in cities" :key="city.id" :value="city.id">{{
                city.name }}</option>
            </select>
          </div>




          <div class="col-lg-auto col-md-4 col-sm-6 mt-sm-0 mt-3 ms-lg-auto">
            <select :key="'price-'+(priceRanges?.length || 0)" ref="priceDropdownRef" id="priceDropdown" v-model="selectedPriceRange"
              class="me-5 form-select select2" :disabled="isEmpty">
              <option value="">{{ $t('landingpage.all_price') }}</option>
              <option :value="price" v-for="price in priceRanges" :key="price">{{ CURRENCY_SYMBOL }} {{ price }}
              </option>
            </select>
          </div>

          <!-- Clear Filters Button -->
          <div class="col-sm-2 mt-sm-0 mt-3">
            <button 
              @click="clearAllFilters" 
              class="form-select text-start"
              :disabled="!hasActiveFilters"
              :title="$t('Clear all filters')"
              style="background-color: transparent; border: 1px solid #ced4da; cursor: pointer;"
            >
              <i class="fas fa-times me-2"></i>
              {{ $t('Clear Filters') }}
            </button>
          </div>

        </div>
      </div>

    </div>

    <div class="table-responsive rounded py-4">
      <table id="datatable" ref="tableRef" class="table custom-card-table service-card-table"></table>
    </div>

  </section>
</template>

<script setup>
import ServiceCard from '../components/ServiceCard.vue';
import ServiceShimmer from '../shimmer/ServiceShimmer.vue';
import { computed, onMounted, ref, watch } from 'vue';
import { useSection } from '../store/index';
import { useObserveSection } from '../hooks/Observer';
import useDataTable from '../hooks/Datatable'

const CURRENCY_SYMBOL = ref(window.defaultCurrencySymbol)

const categoryDropdownRef = ref(null);
const subCategoryDropdownRef = ref(null);
// const providerDropdownRef = ref(null);
const countryDropdownRef = ref(null);
const cityDropdownRef = ref(null);
const priceDropdownRef = ref(null);
// const sortOptionRef = ref(null);
const props = defineProps(['link', 'isEmpty', 'service']);

const isEmpty = props.isEmpty;
const countries = ref([]);
const cities = ref([]);
const sub_category_data = ref([]);

const selectedCountry = ref('');
// watch(() => selectedCountry.value, () => ajaxReload())

const selectedCity = ref('');
watch(() => selectedCity.value, () => ajaxReload())

const selectedCategory = ref('')
watch(() => selectedCategory.value, () => ajaxReload())

const selectedSubCategory = ref('')
watch(() => selectedSubCategory.value, () => ajaxReload())

// const selectedProvider = ref('')
// watch(() => selectedProvider.value, () => ajaxReload())

const selectedPriceRange = ref('')
watch(() => selectedPriceRange.value, () => ajaxReload())

// const selectedSortOption = ref('')
// watch(() => selectedSortOption.value, () => ajaxReload())

const search = ref('')
watch(() => search.value, () => ajaxReload())

const columns = ref([
  { data: 'name', title: '', orderable: false, order: 'desc' }
]);

const ajaxReload = () => window.$(tableRef.value).DataTable().ajax.reload(null, false)
const tableRef = ref(null);

useDataTable({
  tableRef: tableRef,
  columns: columns.value,
  url: props.link,
  dom: '<"row align-items-center"><"table-responsive my-3" rt><"row align-items-center" <"col-md-6" l><"col-md-6 mt-md-0 mt-3" p>><"clear">',
  advanceFilter: () => {
    return {
      selectedCategory: selectedCategory.value,
        selectedSubCategory: selectedSubCategory.value,
      // selectedProvider: selectedProvider.value,
      selectedCountry: selectedCountry.value,
      selectedCity: selectedCity.value,
      selectedPriceRange: selectedPriceRange.value,
      // selectedSortOption: selectedSortOption.value,
      search: search.value,
    }
  }
});

const store = useSection();
const service_data = computed(() => store.service_list_data);
const featured_category_data = computed(() => store.featured_category_list_data);
const category_data = computed(() => store.categries_list_data.data);
// const provider_data = computed(() => store.provider_list_data);

const minPrice = computed(() => Math.min(...service_data.value.map(item => item.price)));
const maxPrice = computed(() => Math.max(...service_data.value.map(item => item.price)));

const priceRanges = computed(() => {
  const range = maxPrice.value - minPrice.value;
  const step = 10;
  const count = Math.ceil(range / step);

  const ranges = Array.from({ length: count + 1 }, (_, index) => ({
    min: minPrice.value + index * step,
    max: minPrice.value + (index + 1) * step,
  }));

  return ranges.map(range => `${range.min}-${range.max}`);
});

const loadServiceData = async () => {
  try {
    await store.get_service_list({ per_page: 'all' });
  } catch (e) { console.error('load_service_list failed', e); }
};

const loadCategoryData = async () => {
  try {
    await store.get_categries_list({ per_page: 'all' });
  } catch (e) { console.error('load_categories failed', e); }
}

// const loadProviderData = async () => {
//   try {
//     await store.get_provider_list({ per_page: 'all', user_type: 'provider' });
//   } catch (e) { console.error('load_providers failed', e); }
// }

const loadFeaturedCategoryData = async () => {
  try {
    await store.get_featured_category_list({ is_featured: 1 });
  } catch (e) { console.error('load_featured_categories failed', e); }
};


onMounted(() => {
  // Initialize Select2 only if not already initialized
  const initSelect2 = (element) => {
    if (element && !$(element).hasClass('select2-hidden-accessible')) {
      $(element).select2({
        width: '100%',
        dropdownParent: $(element).parent()
      });
    }
  };

  // Initialize select2 after a tick so Vue renders new options
  setTimeout(() => {
    initSelect2(categoryDropdownRef.value);
    initSelect2(subCategoryDropdownRef.value);
    initSelect2(countryDropdownRef.value);
    initSelect2(cityDropdownRef.value);
    initSelect2(priceDropdownRef.value);
    // if (sortOptionRef.value) initSelect2(sortOptionRef.value);
  });

  $(categoryDropdownRef.value).on('change', function () {
    selectedCategory.value = $(this).val();
    loadSubCategories($(this).val());
  });
  $(subCategoryDropdownRef.value).on('change', function () {
    selectedSubCategory.value = $(this).val();
  });
  // provider filter removed

  $(countryDropdownRef.value).on('change', function () {
    selectedCountry.value = $(this).val();
    if ($(this).val()) {
      loadCities($(this).val());
    } else {
      cities.value = [];
      selectedCity.value = '';
      $(cityDropdownRef.value).val('').trigger('change');
    }
  });
  $(cityDropdownRef.value).on('change', function () {
    selectedCity.value = $(this).val();
  });

  $(priceDropdownRef.value).on('change', function () {
    selectedPriceRange.value = $(this).val();
  });
  // sort filter removed

  // Load filters first so dropdowns have data before select2 initializes options rendering
  Promise.all([
    loadCategoryData(),
    loadCountries()
  ]).then(() => {
    // Optionally load dependent datasets
    loadFeaturedCategoryData();
    loadServiceData();
  });
});


const refreshDropdowns = () => {
  $(categoryDropdownRef.value).val('').trigger('change');
  $(subCategoryDropdownRef.value).val('').trigger('change');
  $(providerDropdownRef.value).val('').trigger('change');
  $(countryDropdownRef.value).val('').trigger('change');
  $(cityDropdownRef.value).val('').trigger('change');
  $(priceDropdownRef.value).val('').trigger('change');
  $(sortOptionRef.value).val('').trigger('change');
}
const loadCountries = async () => {
  try {
    const response = await fetch('/countries');
    countries.value = await response.json();
  } catch (error) {
    console.error('Error loading countries:', error);
  }
};
const loadCities = async (country_id) => {
  try {
    const response = await fetch('/cities?country_id=' + country_id);
    cities.value = await response.json();
  } catch (error) {
    console.error('Error loading cities:', error);
  }
};
const loadSubCategories = async (cat_id) => {
  try {
    const response = await fetch('/sub-categories?cat_id=' + cat_id);
      sub_category_data.value = await response.json();
  } catch (error) {
    console.error('Error loading cities:', error);
  }
};
const checkDropdowns = computed(() => {
  return selectedProvider.value || selectedPriceRange.value || selectedSortOption.value || selectedCity.value
});

// Check if any filters are active
const hasActiveFilters = computed(() => {
  return selectedCategory.value || 
         selectedSubCategory.value || 
         selectedProvider.value || 
         selectedCountry.value || 
         selectedCity.value || 
         selectedPriceRange.value || 
         selectedSortOption.value || 
         search.value;
});

const clearSearch = () => {
  search.value = '';
}

// Clear all filters function
const clearAllFilters = () => {
  // Reset all filter values
  selectedCategory.value = '';
  selectedSubCategory.value = '';
  selectedProvider.value = '';
  selectedCountry.value = '';
  selectedCity.value = '';
  selectedPriceRange.value = '';
  selectedSortOption.value = '';
  search.value = '';
  
  // Reset dropdowns using existing function
  refreshDropdowns();
  
  // Clear subcategories and cities when clearing filters
  sub_category_data.value = [];
  cities.value = [];
  
  // Reload the data table
  ajaxReload();
}

</script>
