import {constants as alertConstants} from '#/main/core/layout/alert/constants'

const ALERT_REGISTRATION = {
  [alertConstants.ALERT_STATUS_PENDING]: {
    title: 'Inscription en cours',
    message: 'Veuillez patienter pendant que nous créons votre compte.'
  },
  [alertConstants.ALERT_STATUS_SUCCESS]: {
    title: 'Inscription réussie',
    message: 'Amazing ! Your account has been successfully created.'
  },
  [alertConstants.ALERT_STATUS_WARNING]: {
    title: 'Echec de l\'inscription',
    message: 'Please fix your data before we can create your account.'
  },
  [alertConstants.ALERT_STATUS_ERROR]: {
    title: 'Echec de l\'inscription',
    message: 'Sorry, we cannot create your account.'
  }
}

export const constants = {
  ALERT_REGISTRATION
}
