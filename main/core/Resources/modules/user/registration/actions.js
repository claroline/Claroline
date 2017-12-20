import {API_REQUEST} from '#/main/core/api/actions'

export const actions = {}

actions.createUser = (user, onCreated) => ({
  [API_REQUEST]: {
    url: ['apiv2_user_create'],
    messages: {
      pending: {
        title: 'Inscription en cours',
        message: 'Veuillez patienter pendant que nous créons votre compte.'
      },
      success: {
        title: 'Inscription réussie',
        message: 'Amazing ! Your account has been successfully created.'
      },
      warning: {
        title: 'Echec de l\'inscription',
        message: 'Please fix your data before we can create your account.'
      },
      error: {
        title: 'Echec de l\'inscription',
        message: 'Sorry, we cannot create your account.'
      }
    },
    request: {
      method: 'POST',
      body: JSON.stringify(user)
    },
    success: () => {
      onCreated()
    }

    /*error: (data, dispatch) => {
      /!*data.json().then(errors => {
        dispatch(actions.validateUser(errors))
      })*!/
      // todo set errors
    }*/
  }
})
