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
              <input class="form-check-input" type="radio" name="payment_method" v-model="payment_method" id="wallet" value="wallet" />
              <label class="form-check-label h6 fw-normal text-capitalize" for="wallet">{{ $t('messages.wallet') }}</label>
            </div>
            <div class="form-check" v-if="isPaypalEnabled">
              <input class="form-check-input" type="radio" name="payment_method" v-model="payment_method" id="paypal" value="paypal" />
              <label class="form-check-label h6 fw-normal text-capitalize" for="paypal">{{ $t('PayPal') }}</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="payment_method" v-model="payment_method" id="bank_transfer" value="bank_transfer" @change="onPaymentMethodChange" />
              <label class="form-check-label h6 fw-normal text-capitalize" for="bank_transfer">{{ $t('Bank Transfer') || 'Bank Transfer' }}</label>
            </div>
          </div>
            <p>
                {{ props.payment_type === 'paid' ? $t('messages.total_amount') : $t('messages.advance_pay') }}:
                {{ formatCurrencyVue(paymentDisplayAmount) }}
            </p>

            <p>{{ $t('messages.wallet_balance') }}: {{ formatCurrencyVue(wallet_amount) }}</p>
          <div class="mt-3">
            <div class="d-inline-flex align-items-center flex-wrap gap-3">
              <button class="btn btn-primary" type="submit">
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
            <i class="bi bi-bank2 me-2"></i> {{ $t('Bank Transfer Details') || 'Bank Transfer Details' }}
          </h5>
          <button type="button" class="btn-close btn-close-white" aria-label="Close" @click.stop.prevent="hideBankInfoModal"></button>
        </div>
        <div class="modal-body p-4">
          <div class="mb-4">
            <p class="fs-6 mb-0">
              <strong>
                Please pay the amount of <span class="text-success">{{ formatCurrencyVue(paymentDisplayAmount) }}</span>
                via bank transfer using the details below:
              </strong>
            </p>
          </div>
          <div class="row g-3">
            <div class="col-md-6">
              <div class="bg-light p-3 rounded border">
                <h6 class="text-primary mb-2"><i class="bi bi-credit-card-2-front-fill me-2"></i>Bank Information</h6>
                <ul class="list-unstyled mb-0">
                  <li><strong>Bank Name:</strong> Norisbank</li>
                  <li><strong>Country:</strong> Germany</li>
                  <li><strong>Account Number:</strong> 4776167</li>
                  <li><strong>IBAN:</strong> DE57760260000477616700</li>
                  <li><strong>BIC/Swift:</strong> NORDSDE71XXX</li>
                </ul>
              </div>
            </div>
            <div class="col-md-6">
              <div class="bg-light p-3 rounded border h-100">
                <h6 class="text-primary mb-2"><i class="bi bi-info-circle-fill me-2"></i>Instructions</h6>
                <p class="mb-2">Mention your Booking ID <code>#{{ booking_id }}</code> in the transfer reference.</p>
                <p class="mb-0">
                  <strong>Send Proof of Payment (screenshot or pdf Document) to:</strong>
                  <a href="mailto:billing@frobster.com">billing@frobster.com</a>
                </p>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer border-top-0 px-4 pb-4">
          <button type="button" class="btn btn-secondary rounded-pill px-4" @click.stop.prevent="hideBankInfoModal">Close</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, defineProps, computed, onMounted, onBeforeUnmount } from 'vue'
import { Modal } from 'bootstrap'
import * as yup from 'yup'
import { useField, useForm } from 'vee-validate'
import {
  GET_PAYMENT_METHOD,
  GET_STRIPE_PAYMENT_URL,
  WALLET_PAYMENT_API,
  PAYMENT_GATEWAY_LIST,
    BANK_TRANSFER_PAYMENT_API
} from '../data/api'
import Swal from 'sweetalert2'
import { confirmcancleSwal, confirmcancleWallet } from '../data/utilities'
import Wallet from '../components/Wallet.vue'

const props = defineProps(['booking_id', 'customer_id', 'discount', 'total_amount', 'advance_payment_amount', 'wallet_amount', 'payment_type', 'total_advance_paid_amount', 'total_booking_amount', 'advance_percentage'])

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
    if (values.wallet_amount > 0 && values.total_amount < values.wallet_amount) {
      IsLoading.value = 1
      values.txn_id = `#${props.booking_id}`
      values.payment_status = values.type === 'advance_payment' ? 'advanced_paid' : 'paid'

      confirmcancleSwal({ title: 'Confirm Payment', subtitle: 'Do you want to pay with Wallet?' }).then(async (result) => {
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
          Swal.fire({ title: 'Done', text: responseData.message, icon: 'success', iconColor: '#5F60B9' }).then(() => {
            const baseUrl = document.querySelector('meta[name="baseUrl"]').getAttribute('content')
            window.location.href = baseUrl + '/booking-list'
          })
        } else {
          Swal.fire({ title: 'Error', text: 'Something Went Wrong!', icon: 'error', iconColor: '#5F60B9' })
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
      values.txn_id = `#${props.booking_id}`
      values.payment_status = values.type === 'advance_payment' ? 'advanced_paid' : 'paid'

      confirmcancleSwal({ title: 'Bank Transfer Selected', subtitle: 'Please complete your payment via bank transfer and email proof to billing@frobster.com' }).then(async (result) => {
          IsLoading.value = 0
          // if (!result.isConfirmed) return
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
              Swal.fire({ title: 'Done', text: responseData.message, icon: 'success', iconColor: '#5F60B9' }).then(() => {
                  const baseUrl = document.querySelector('meta[name="baseUrl"]').getAttribute('content')
                  window.location.href = baseUrl + '/booking-list'
              })
          } else {
              Swal.fire({ title: 'Error', text: 'Something Went Wrong!', icon: 'error', iconColor: '#5F60B9' })
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
              Swal.fire({ title: 'Error', text: 'Check Your Paypal key Integration!', icon: 'error', iconColor: '#5F60B9' })
              IsLoading.value = 0
          }
      } else {
          Swal.fire({ title: 'Error', text: 'Something Went Wrong!', icon: 'error', iconColor: '#5F60B9' })
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
        Swal.fire({ title: 'Error', text: 'Check Your Stripe key Integration!', icon: 'error', iconColor: '#5F60B9' })
        IsLoading.value = 0
      }
    } else {
      Swal.fire({ title: 'Error', text: 'Something Went Wrong!', icon: 'error', iconColor: '#5F60B9' })
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
        Swal.fire({ title: 'Error', text: 'Invalid PayPal response.', icon: 'error', iconColor: '#5F60B9' })
      }
    } else {
      Swal.fire({ title: 'Error', text: 'Failed to redirect to PayPal.', icon: 'error', iconColor: '#5F60B9' })
    }
  } catch (error) {
    Swal.fire({ title: 'Error', text: 'Something went wrong.', icon: 'error', iconColor: '#5F60B9' })
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
      Swal.fire({ title: 'Error', text: responseData.message || 'Something went wrong.', icon: 'error', iconColor: '#5F60B9' })
    }
  } else {
    Swal.fire({ title: 'Error', text: 'Stripe redirect failed.', icon: 'error', iconColor: '#5F60B9' })
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

const hideBankInfoModal = () => {
  const modal = getBankModalInstance()
  if (modal && isBankModalOpen.value) modal.hide()
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
