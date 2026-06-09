import 'sweetalert2/dist/sweetalert2.min.css';
import Swal from 'sweetalert2';

const getConfirmText = () => window.i18n?.global?.t('messages.confirm') || 'Confirm'

export const confirmSwal = async ({title}) => {
    return await Swal.fire({
        title: title,
        icon: 'success',
        showCancelButton: true,
        confirmButtonColor: '#3333ff',
        cancelButtonColor: '#d33',
        confirmButtonText: getConfirmText(),
        iconColor: '#3333ff'
      }).then((result) => {
        return result
      })
}

export const confirmcancleSwal = async ({title,subtitle,text}) => {
    return await Swal.fire({
        title: title,
        html: subtitle || text,
        icon: 'success',
        showCancelButton: true,
        confirmButtonColor: '#3333ff',
        cancelButtonColor: '#858482',
        confirmButtonText: getConfirmText(),
        iconColor: '#3333ff'
      }).then((result) => {
        return result
      })
}

export const confirmcancleWallet = async ({title}) => {
  return await Swal.fire({
      title: title,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3333ff',
      cancelButtonColor: '#858482',
      confirmButtonText: getConfirmText(),
      iconColor: '#3333ff'
    }).then((result) => {
      return result
    })
}
