<template>
  <section ref="servicesection">

    <div class="row align-items-center">
      <div class="col-lg-12">
        <div class="row gx-1">
          <div class="col-sm-2">
            <select ref="categoryDropdownRef" id="categoryDropdown" v-model="selectedCategory"
              class="me-5 form-select select2" :disabled="isEmpty">
              <option value="">{{ $t('landingpage.all_categories') }}</option>
              <option v-for="category in category_data" :key="category.id" :value="category.id">{{ category.name }}
              </option>
            </select>

          </div>
          <div class="col-sm-2">
            <select ref="categoryDropdownRef" id="categoryDropdown" v-model="selectedCategory"
              class="me-5 form-select select2" :disabled="isEmpty">
              <option value="">{{ $t('Sub Categories') }}</option>
              <option v-for="category in category_data" :key="category.id" :value="category.id">{{ category.name }}
              </option>
            </select>

          </div>
          <div class="col-sm-2 mt-sm-0 mt-3">
            <select ref="providerDropdownRef" id="providerDropdown" v-model="selectedProvider"
              class="me-5 form-select select2" :disabled="isEmpty">
              <option value="">{{ $t('landingpage.all_providers') }}</option>
              <option v-for="providers in provider_data" :key="providers.id" :value="providers.id">{{
                providers.first_name }}</option>
            </select>
          </div>
          <div class="col-sm-2 mt-sm-0 mt-3">
            <select ref="countryDropdownRef" id="countryDropdown" v-model="selectedCountry"
              class="me-5 form-select select2">
              <option value="">{{ $t('Filter by Country') }}</option>
              <option v-for="country in countries" :key="country.id" :value="country.id">{{ country.name }}</option>
            </select>
          </div>


          <div class="col-sm-2 mt-sm-0 mt-3">
            <select ref="providerDropdownRef" id="providerDropdown" v-model="selectedProvider"
              class="me-5 form-select select2" :disabled="isEmpty">
              <option value="">{{ $t('Filter by City') }}</option>
              <option v-for="providers in provider_data" :key="providers.id" :value="providers.id">{{
                providers.first_name }}</option>
            </select>
          </div>




          <div class="col-sm-2 mt-sm-0 mt-3">
            <select ref="priceDropdownRef" id="priceDropdown" v-model="selectedPriceRange"
              class="me-5 form-select select2" :disabled="isEmpty">
              <option value="">{{ $t('landingpage.all_price') }}</option>
              <option :value="price" v-for="price in priceRanges" :key="price">{{ CURRENCY_SYMBOL }} {{ price }}
              </option>
            </select>
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
const providerDropdownRef = ref(null);
const priceDropdownRef = ref(null);
const sortOptionRef = ref(null);
const props = defineProps(['link', 'isEmpty', 'service']);

const isEmpty = props.isEmpty;
const countries = ref([]);
const selectedCountry = ref('');
const selectedCategory = ref('')
watch(() => selectedCategory.value, () => ajaxReload())

const selectedProvider = ref('')
watch(() => selectedProvider.value, () => ajaxReload())

const selectedPriceRange = ref('')
watch(() => selectedPriceRange.value, () => ajaxReload())

const selectedSortOption = ref('')
watch(() => selectedSortOption.value, () => ajaxReload())

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
      selectedProvider: selectedProvider.value,
      selectedPriceRange: selectedPriceRange.value,
      selectedSortOption: selectedSortOption.value,
      search: search.value,
    }
  }
});

const store = useSection();
const service_data = computed(() => store.service_list_data);
const featured_category_data = computed(() => store.featured_category_list_data);
const category_data = computed(() => store.categries_list_data.data);
const provider_data = computed(() => store.provider_list_data);

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

const loadServiceData = () => {
  store.get_service_list({ per_page: 'all' });
};

const loadCategoryData = () => {
  store.get_categries_list({ per_page: 'all' });
}

const loadProviderData = () => {
  store.get_provider_list({ per_page: 'all', user_type: 'provider' });
}

const loadFeaturedCategoryData = () => {
  store.get_featured_category_list({ is_featured: 1 });
};


onMounted(() => {
  $(categoryDropdownRef.value).select2();
  $(providerDropdownRef.value).select2();
  $(priceDropdownRef.value).select2();
  $(sortOptionRef.value).select2();

  $(categoryDropdownRef.value).on('change', function () {
    selectedCategory.value = $(this).val();
  });
  $(providerDropdownRef.value).on('change', function () {
    selectedProvider.value = $(this).val();
  });
  $(priceDropdownRef.value).on('change', function () {
    selectedPriceRange.value = $(this).val();
  });
  $(sortOptionRef.value).on('change', function () {
    selectedSortOption.value = $(this).val();
  });

  loadServiceData();
  loadCategoryData();
  loadProviderData();
  loadFeaturedCategoryData();
  loadCountries(); // ✅ Add this line to fetch countries on mount
});


const refreshDropdowns = () => {
  $(categoryDropdownRef.value).val('').trigger('change');
  $(providerDropdownRef.value).val('').trigger('change');
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
const checkDropdowns = computed(() => {
  return selectedCategory.value || selectedProvider.value || selectedPriceRange.value || selectedSortOption.value
});

const clearSearch = () => {
  search.value = '';
}

</script>
