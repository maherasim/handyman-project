<template>
  <div class="booking-list-content">
    <h5 class="mt-5 mb-0 text-capitalize">{{ $t('landingpage.payment_option') }}</h5>
    <div class="mt-0">
      <form @submit.prevent="formSubmit">
        <template v-if="!isChildComponentVisible">
          <h6 class="mb-2 mt-3 text-capitalize">{{ $t('messages.payment_method') }}</h6>
          <div class="d-flex align-items-center flex-wrap gap-3">
            <div class="form-check" v-if="isStripeEnabled">
              <input class="form-check-input" type="radio" name="payment_method" v-model="payment_method" id="stripe" value="stripe" />
              <label class="form-check-label h6 fw-normal text-capitalize" for="stripe">{{ $t('messages.stripe') }}</label>
            </div>
            <div class="form-check">   
              <input class="form-check-input" type="radio" name="payment_method" v-model="payment_method" id="wallet" value="wallet"
                :disabled="!canUseWallet" />
              <label class="form-check-label h6 fw-normal text-capitalize" for="wallet" :class="{ 'text-muted': !canUseWallet }">
                {{ $t('messages.wallet') }}
                <span v-if="!canUseWallet" class="small">({{ $t('messages.insufficient_balance') }})</span>
              </label>
            </div>           
            
            <div class="form-check" v-if="isPaypalEnabled">
              <input class="form-check-input" type="radio" name="payment_method" v-model="payment_method" id="paypal" value="paypal" />
              <label class="form-check-label h6 fw-normal text-capitalize" for="paypal">{{ $t('messages.paypal') }}</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="payment_method" v-model="payment_method" id="bank_transfer" value="bank_transfer" @change="onPaymentMethodChange" />
              <label class="form-check-label h6 fw-normal text-capitalize" for="bank_transfer">{{ $t('messages.bank_transfer') }}</label>
            </div>
          </div>
            <div class="mt-3 p-3 rounded border bg-light">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted text-capitalize">{{ paymentLabel }}</span>
                <strong class="text-primary fs-5">{{ formatCurrencyVue(paymentDisplayAmount) }}</strong>
              </div>
              <div class="d-flex justify-content-between align-items-center border-top pt-2">
                <span class="text-muted text-capitalize">{{ $t('messages.wallet_balance') }}</span>
                <span :class="canUseWallet ? 'text-success' : 'text-danger'" class="fw-semibold">
                  {{ formatCurrencyVue(wallet_amount) }}
                </span>
              </div>
            </div>
          <div class="mt-3">
            <div class="d-inline-flex align-items-center flex-wrap gap-3">
              <button class="btn btn-primary" type="submit" :disabled="isProceedDisabled || IsLoading == 1">
                <span v-if="IsLoading == 1" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                <span v-else>{{ $t('landingpage.Proceed_To_Payment') }}</span>
              </button>
            </div>
          </div>
        </template>
        <component :is="currentComponent" :service="service" :booking_id="booking_id" :discount="discount" :advance_payment_amount="advance_payment_amount" :customer_id="customer_id" :wallet_amount="wallet_amount" v-if="isChildComponentVisible" />
      </form>
    </div>
  </div>
                                                
  <!-- Bank Transfer Info Modal -->
  <div class="modal fade" id="bankTransferModal" ref="bankModalRef" tabindex="-1" aria-labelledby="bankTransferModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content shadow-lg border-0 rounded-4">
        <div class="modal-header bg-primary text-white rounded-top-4">
          <h5 class="modal-title" id="bankTransferModalLabel">
            <i class="bi bi-bank2 me-2"></i> {{ $t('messages.bank_transfer_details') }}
          </h5>
          <button type="button" class="btn-close btn-close-white" aria-label="Close" @click.stop.prevent="hideBankInfoModal"></button>
        </div>
        <div class="modal-body p-4">
          <div class="mb-4">
            <p class="fs-6 mb-0">
              <strong>
                {{ $t('messages.please_pay_amount') }} <span class="text-success">{{ formatCurrencyVue(paymentDisplayAmount) }}</span>
                {{ $t('messages.via_bank_transfer_below') }}
              </strong>
            </p>
          </div>
          <div class="mb-3">
            <h6 class="text-primary mb-3"><strong>{{ $t('messages.for_local_international_transfers') }}</strong></h6>
          </div>
          <div class="row g-3">
            <div class="col-md-6">
              <div class="bg-light p-3 rounded border">
                <h6 class="text-primary mb-2"><i class="bi bi-credit-card-2-front-fill me-2"></i>{{ $t('messages.bank_information') }}</h6>
                <ul class="list-unstyled mb-0">
                  <li><strong>{{ $t('messages.pjr_bank_recipient') }}</strong> {{ bankConfig.recipient }}</li>
                  <li><strong>{{ $t('messages.pjr_bank_iban') }}</strong> {{ bankConfig.iban }}</li>
                  <li><strong>{{ $t('messages.pjr_bank_bic') }}</strong> {{ bankConfig.bic }}</li>
                  <li><strong>{{ $t('messages.bank_transfer_bank_name') }}:</strong> {{ bankConfig.bank_name }}</li>
                  <li><strong>{{ $t('messages.bank_transfer_bank_address') }}:</strong> <span style="white-space: pre-line">{{ bankConfig.bank_address }}</span></li>
                </ul>
              </div>
            </div>
            <div class="col-md-6">
              <div class="bg-light p-3 rounded border h-100">
                <h6 class="text-primary mb-2"><i class="bi bi-info-circle-fill me-2"></i>{{ $t('messages.instructions') }}</h6>
                <p class="mb-2">{{ $t('messages.mention_booking_id_reference', { id: booking_id }) }}</p>
                <p class="mb-0">
                  <strong>{{ $t('messages.send_proof_of_payment') }}</strong>
                  <a :href="`mailto:${bankConfig.email}`">{{ bankConfig.email }}</a>
                </p>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer border-top-0 px-4 pb-4">
          <button type="button" class="btn btn-secondary rounded-pill px-4" @click.stop.prevent="hideBankInfoModal">{{ $t('messages.close') }}</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, defineProps, computed, onMounted, onBeforeUnmount } from 'vue'
import { useI18n } from 'vue-i18n'
import { Modal } from 'bootstrap'
import * as yup from 'yup'
import { useField, useForm } from 'vee-validate'
import {
  GET_PAYMENT_METHOD,
  GET_STRIPE_PAYMENT_URL,
  WALLET_PAYMENT_API,
  PAYMENT_GATEWAY_LIST,
  BANK_TRANSFER_PAYMENT_API,
  BANK_TRANSFER_SETTINGS_API
} from '../data/api'
import Swal from 'sweetalert2'
import { confirmcancleSwal, confirmcancleWallet } from '../data/utilities'
import Wallet from '../components/Wallet.vue'

const { t, locale } = useI18n()
const props = defineProps(['booking_id', 'customer_id', 'discount', 'total_amount', 'advance_payment_amount', 'wallet_amount', 'payment_type', 'total_advance_paid_amount', 'total_booking_amount', 'advance_percentage'])

const bankConfig = ref(window.bankTransferConfig || {})

const fetchBankTransferSettings = async () => {
  try {
    const lang = (locale.value || 'en').split('-')[0]
    const res = await fetch(`${BANK_TRANSFER_SETTINGS_API}?language=${lang}`)
    if (res.ok) {
      const json = await res.json()
      if (json.data) {
        // API returns single object when ?language= is passed, array without it
        bankConfig.value = Array.isArray(json.data) ? (json.data[0] || {}) : json.data
      }
    }
  } catch (e) {
    console.error('Bank transfer settings fetch failed:', e)
  }
}

const paymentLabel = computed(() => {
  if (props.payment_type === 'paid') return t('messages.total_amount')
  const pct = Number(props.advance_percentage)
  if (Number.isFinite(pct) && pct > 0) return `${t('messages.advance_pay')} (${pct}%)`
  return t('messages.advance_pay')
})

const remainingAmount = computed(() => {
  return props.advance_payment_amount != null ? props.advance_payment_amount : props.total_booking_amount
})

const paymentDisplayAmount = computed(() => {
    if (props.payment_type === 'paid') {
        const advancePaid = props.total_advance_paid_amount || 0;
        return props.total_booking_amount - advancePaid;
    } else {
        const bookedAdvance = Number(props.total_advance_paid_amount) || 0;
        return bookedAdvance > 0
            ? bookedAdvance
            : (props.total_booking_amount * props.advance_percentage) / 100;
    }
});

const canUseWallet = computed(() => {
  const balance = Number(props.wallet_amount) || 0;
  const amountDue = Number(paymentDisplayAmount.value) || 0;
  return balance >= amountDue && amountDue > 0;
});

const isProceedDisabled = computed(() => {
  if (payment_method.value !== 'wallet') return false;
  const balance = Number(props.wallet_amount) || 0;
  const amountDue = Number(paymentDisplayAmount.value) || 0;
  return balance <= 0 || balance < amountDue;
});



const paymentGatewayList = ref([])

const fetchPaymentGatewayList = async () => {
  try {
    const response = await fetch(PAYMENT_GATEWAY_LIST)
    if (response.ok) {
      const responseData = await response.json()
      paymentGatewayList.value = Array.isArray(responseData.data) ? responseData.data : []
    } else {
      throw new Error('Failed to fetch payment gateway list')
    }
  } catch (error) {
    console.error('Error fetching payment gateway list:', error)
  }
}

onMounted(async () => {
  await fetchPaymentGatewayList()
  fetchBankTransferSettings()
  setFormData(defaultData())
})

const isStripeEnabled = computed(() => {
  return paymentGatewayList.value.some(gateway => gateway.type === 'stripe' && gateway.status === 1)
})

const isPaypalEnabled = computed(() => {
  return paymentGatewayList.value.some(gateway => gateway.type === 'paypal' && gateway.status === 1)
})

const IsLoading = ref(0)

const defaultData = () => {
  errorMessages.value = {}
  return {
    payment_method: 'stripe',
  }
}

const setFormData = (data) => {
  resetForm({
    values: {
      payment_method: data.payment_method,
    },
  })
}

const validationSchema = yup.object({})
const { handleSubmit, errors, resetForm } = useForm({ validationSchema })
const { value: payment_method } = useField('payment_method')
const errorMessages = ref({})

const formSubmit = handleSubmit(async (values) => {
  if (IsLoading.value === 1) return
   let advance_payment = (Number(props.total_advance_paid_amount) && Number(props.total_advance_paid_amount) > 0)
       ? Number(props.total_advance_paid_amount)
       : (props.total_booking_amount * props.advance_percentage) / 100;
  values.booking_id = props.booking_id
  values.customer_id = props.customer_id
  values.discount = props.discount
  values.payment_type = values.payment_method
  values.wallet_amount = props.wallet_amount
  // Calculate remaining amount for remaining payments
  const remaining_amount = props.payment_type == 'paid' 
    ? (props.total_booking_amount - (Number(props.total_advance_paid_amount) || 0))
    : advance_payment
  
  values.total_amount = remaining_amount
  values.advance_paid_amount = props.payment_type == 'paid' ? null : advance_payment

  values.type = props.payment_type == 'paid' ? 'remaining' : 'advance_payment'
  values.total_amount = Number(values.total_amount).toFixed(2)

  const csrfToken = document.querySelector('meta[name="csrf-token"]').content

  if (values.payment_type === 'wallet') {
    const walletBal = Number(props.wallet_amount) || 0;
    if (walletBal <= 0) {
      Swal.fire({ title: '', text: t('messages.insufficient_wallet_balance_choose_another'), icon: 'warning', iconColor: '#3333ff' });
      return;
    }
    if (values.wallet_amount > 0 && values.total_amount < values.wallet_amount) {
      IsLoading.value = 1
      values.txn_id = `#${props.booking_id}`
      values.payment_status = values.type === 'advance_payment' ? 'advanced_paid' : 'paid'

      confirmcancleSwal({ title: t('messages.confirm_payment'), subtitle: t('messages.pay_with_wallet_confirm') }).then(async (result) => {
        IsLoading.value = 0
        if (!result.isConfirmed) return
        IsLoading.value = 1
        const response = await fetch(WALLET_PAYMENT_API, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
          },
          body: JSON.stringify(values),
        })

        if (response.ok) {
          const responseData = await response.json()
          Swal.fire({ title: t('messages.done'), text: responseData.message, icon: 'success', iconColor: '#3333ff' }).then(() => {
            const baseUrl = document.querySelector('meta[name="baseUrl"]').getAttribute('content')
            window.location.href = baseUrl + '/booking-list'
          })
        } else {
          Swal.fire({ title: t('messages.error'), text: t('messages.something_went_wrong'), icon: 'error', iconColor: '#3333ff' })
        }
        IsLoading.value = 0
      })
    } else {
      confirmcancleWallet({ title: 'Do you want to Top Up your Wallet now?' }).then((result) => {
        IsLoading.value = 0
        if (!result.isConfirmed) return
        showChildComponent()
      })
    }
  } else if (values.payment_type === 'bank_transfer') {
      IsLoading.value = 1
      values.txn_id = `#${props.booking_id}`
      values.payment_status = values.type === 'advance_payment' ? 'advanced_paid' : 'paid'

      confirmcancleSwal({ title: t('messages.bank_transfer_selected'), subtitle: t('messages.bank_transfer_email_proof') }).then(async (result) => {
          if (!result.isConfirmed) {
            IsLoading.value = 0
            return
          }
          IsLoading.value = 1
          const response = await fetch(BANK_TRANSFER_PAYMENT_API, {
              method: 'POST',
              headers: {
                  'Content-Type': 'application/json',
                  'X-CSRF-TOKEN': csrfToken,
              },
              body: JSON.stringify(values),
          })

          if (response.ok) {
              const responseData = await response.json()
              Swal.fire({ title: t('messages.done'), text: responseData.message, icon: 'success', iconColor: '#3333ff' }).then(() => {
                  const baseUrl = document.querySelector('meta[name="baseUrl"]').getAttribute('content')
                  window.location.href = baseUrl + '/booking-list'
              })
          } else {
              Swal.fire({ title: t('messages.error'), text: t('messages.something_went_wrong'), icon: 'error', iconColor: '#3333ff' })
          }
          IsLoading.value = 0
      });
  } else if (values.payment_type === 'paypal') {
    IsLoading.value = 1
      const response = await fetch(GET_PAYMENT_METHOD, {
          method: 'POST',
          headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': csrfToken,
          },
          body: JSON.stringify(values),
      })
      if (response.ok) {
          const responseData = await response.json()
          if (responseData.payment_geteway_data != null) {
              redirectToPayPal(values)
          } else {
              Swal.fire({ title: t('messages.error'), text: t('messages.check_paypal_integration'), icon: 'error', iconColor: '#3333ff' })
              IsLoading.value = 0
          }
      } else {
          Swal.fire({ title: t('messages.error'), text: t('messages.something_went_wrong'), icon: 'error', iconColor: '#3333ff' })
          IsLoading.value = 0
      }
  } else if (values.payment_type === 'stripe') {
    IsLoading.value = 1
    const response = await fetch(GET_PAYMENT_METHOD, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
      },
      body: JSON.stringify(values),
    })
    if (response.ok) {
      const responseData = await response.json()
      if (responseData.payment_geteway_data != null) {
        Openstripepayment(responseData)
      } else {
        Swal.fire({ title: t('messages.error'), text: t('messages.check_stripe_integration'), icon: 'error', iconColor: '#3333ff' })
        IsLoading.value = 0
      }
    } else {
      Swal.fire({ title: t('messages.error'), text: t('messages.something_went_wrong'), icon: 'error', iconColor: '#3333ff' })
      IsLoading.value = 0
    }
  }
})

const redirectToPayPal = async (values) => {
  try {
    const response = await fetch('/api/paypal/create-payment', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
      },
      body: JSON.stringify(values),
    })

    if (response.ok) {
      const data = await response.json()
      if (data.url) {
        window.location.href = data.url
      } else {
        Swal.fire({ title: t('messages.error'), text: t('messages.invalid_paypal_response'), icon: 'error', iconColor: '#3333ff' })
      }
    } else {
      Swal.fire({ title: t('messages.error'), text: t('messages.failed_redirect_paypal'), icon: 'error', iconColor: '#3333ff' })
    }
  } catch (error) {
    Swal.fire({ title: t('messages.error'), text: t('messages.something_went_wrong'), icon: 'error', iconColor: '#3333ff' })
    console.error(error)
  } finally {
    IsLoading.value = 0
  }
}

const Openstripepayment = async (data) => {
  const csrfToken = document.querySelector('meta[name="csrf-token"]').content
  const res = await fetch(GET_STRIPE_PAYMENT_URL, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': csrfToken,
    },
    body: JSON.stringify(data),
  })

  if (res.ok) {
    const responseData = await res.json()
    if (responseData.url) {
      window.location.href = responseData.url
    } else {
      Swal.fire({ title: t('messages.error'), text: responseData.message || t('messages.something_went_wrong'), icon: 'error', iconColor: '#3333ff' })
    }
  } else {
    Swal.fire({ title: t('messages.error'), text: t('messages.stripe_redirect_failed'), icon: 'error', iconColor: '#3333ff' })
  }
}

const bankModalRef = ref(null)
let bankModalInstance = null
const isBankModalOpen = ref(false)

const getBankModalInstance = () => {
  const modalEl = bankModalRef.value || document.getElementById('bankTransferModal')
  if (!modalEl) return null
  if (bankModalInstance) return bankModalInstance
  bankModalInstance = Modal.getOrCreateInstance ? Modal.getOrCreateInstance(modalEl) : new Modal(modalEl)
  // Track open/close state to avoid double inits and help with logic
  modalEl.addEventListener('shown.bs.modal', () => { isBankModalOpen.value = true })
  modalEl.addEventListener('hidden.bs.modal', () => { isBankModalOpen.value = false })
  return bankModalInstance
}

const showBankInfoModal = () => {
  if (payment_method.value !== 'bank_transfer') return
  const modal = getBankModalInstance()
  if (modal && !isBankModalOpen.value) modal.show()
}

const forceCloseBankModal = () => {
  // Fallback cleanup in case backdrop/body gets stuck due to version conflicts
  document.querySelectorAll('.modal-backdrop').forEach(el => el.parentNode && el.parentNode.removeChild(el))
  document.body.classList.remove('modal-open')
  document.body.style.removeProperty('padding-right')
}

const hideBankInfoModal = () => {
  const modal = getBankModalInstance()
  if (modal) modal.hide()
  // Ensure cleanup after animation completes
  setTimeout(forceCloseBankModal, 300)
}

const onPaymentMethodChange = () => {
  if (payment_method.value === 'bank_transfer') {
    showBankInfoModal()
  } else {
    hideBankInfoModal()
  }
}

const formatCurrencyVue = (value) => {
  return window.currencyFormat !== undefined ? window.currencyFormat(value) : value
}

const isChildComponentVisible = ref(false)
const currentComponent = ref(null)

const showChildComponent = () => {
  currentComponent.value = Wallet
  isChildComponentVisible.value = true
}
</script>
