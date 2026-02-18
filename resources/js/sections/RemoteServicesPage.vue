<template>
    <section ref="remoteservicesection">
      <!-- Search bar: type here then click Search to filter services -->
      <div class="row align-items-center mb-3">
        <div class="col-auto flex-grow-1 pe-2">
          <input
            type="text"
            class="form-control"
            :placeholder="searchPlaceholder"
            v-model="searchInput"
            @keyup.enter="onSearchClick"
            aria-label="Search services"
          />
        </div>
        <div class="col-auto">
          <button type="button" class="btn btn-primary" @click="onSearchClick">
            {{ searchButtonLabel }}
          </button>
        </div>
      </div>
      <div class="table-responsive rounded py-4">
            <table id="remote-service-datatable" ref="tableRef" class="table custom-card-table service-card-table"></table>
      </div>
      
    </section>
  </template>

  <script setup>
  import ServiceCard from '../components/RemoteServiceCard.vue';
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

  const selectedCategory = ref('')
  watch(() => selectedCategory.value, () => ajaxReload())

  const selectedProvider = ref('')
  watch(() => selectedProvider.value, () => ajaxReload())
  
  const selectedPriceRange = ref('')
  watch(() => selectedPriceRange.value, () => ajaxReload())

  const selectedSortOption = ref('')
  watch(() => selectedSortOption.value, () => ajaxReload())

  const search = ref('')
  const searchInput = ref('')
  const searchPlaceholder = ref(typeof window.__categorySearchPlaceholder === 'string' ? window.__categorySearchPlaceholder : 'Search services...')
  const searchButtonLabel = ref(typeof window.__categorySearchButtonLabel === 'string' ? window.__categorySearchButtonLabel : 'Search')
  watch(() => search.value, () => ajaxReload())

  const columns = ref([
    { data: 'name', title: '', orderable: false, order: 'desc'}
  ]);

  const ajaxReload = () => tableRef.value && window.$(tableRef.value).DataTable && window.$(tableRef.value).DataTable().ajax.reload(null, false)
  const tableRef = ref(null);

  const onSearchClick = () => {
    search.value = searchInput.value ? String(searchInput.value).trim() : ''
  }

  useDataTable({
    tableRef: tableRef,
    columns: columns.value,
    url: props.link,
    dom: '<"row align-items-center"><"table-responsive my-3" rt><"row align-items-center" <"col-md-2" l><"col-md-2 mt-md-0 mt-3" p>><"clear">',
    advanceFilter: () => ({
      search: search.value,
      selectedCategory: selectedCategory.value,
      selectedProvider: selectedProvider.value,
      selectedPriceRange: selectedPriceRange.value,
      selectedSortOption: selectedSortOption.value
    }),
    per_page: 6
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

  const loadCategoryData = () =>{
    store.get_categries_list({ per_page: 'all' });
  }

  const loadProviderData = () =>{
    store.get_provider_list({ per_page: 'all', user_type: 'provider' });
  }

  const loadFeaturedCategoryData = () => {
    store.get_featured_category_list({ is_featured: 1 });
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

    initSelect2(categoryDropdownRef.value);
    initSelect2(providerDropdownRef.value);
    initSelect2(priceDropdownRef.value);
    initSelect2(sortOptionRef.value);
    $(categoryDropdownRef.value).on('change', function() {
      selectedCategory.value = $(this).val();
    });
    $(providerDropdownRef.value).on('change', function() {
      selectedProvider.value = $(this).val();
    });
    $(priceDropdownRef.value).on('change', function() {
      selectedPriceRange.value = $(this).val();
    });
    $(sortOptionRef.value).on('change', function() {
      selectedSortOption.value = $(this).val();
    });
    loadServiceData();
    loadCategoryData();
    loadProviderData();
    loadFeaturedCategoryData();
  });
  
  const refreshDropdowns = () => {
    $(categoryDropdownRef.value).val('').trigger('change');
    $(providerDropdownRef.value).val('').trigger('change');
    $(priceDropdownRef.value).val('').trigger('change');
    $(sortOptionRef.value).val('').trigger('change');
  }

  const checkDropdowns = computed(() => {
    return selectedCategory.value || selectedProvider.value || selectedPriceRange.value || selectedSortOption.value
  });

  const clearSearch = () =>{
    search.value = '';
    searchInput.value = '';
  }

  </script>
