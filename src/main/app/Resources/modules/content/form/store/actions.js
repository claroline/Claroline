import isEmpty from 'lodash/isEmpty'
import set from 'lodash/set'

import {makeInstanceAction, makeInstanceActionCreator} from '#/main/app/store/actions'

import {trans} from '#/main/app/intl/translation'
import {API_REQUEST} from '#/main/app/api'
import {actions as alertActions} from '#/main/app/overlays/alert/store'
import {constants as alertConstants} from '#/main/app/overlays/alert/constants'
import {constants as actionConstants} from '#/main/app/action/constants'
import {selectors as formSelect} from '#/main/app/content/form/store/selectors'

/**
 * Reset the form data and cancels all the pending changes already made if any.
 */
export const FORM_RESET          = 'FORM_RESET'

/**
 * Load some data into the form state without toggling the pending state.
 * Data are merged with the current data in the form (see FORM_RESET if you want to override all the form data)
 */
export const FORM_LOAD           = 'FORM_LOAD'
export const FORM_SET_MODE       = 'FORM_SET_MODE'
export const FORM_SET_ERRORS     = 'FORM_SET_ERRORS'
export const FORM_SUBMIT         = 'FORM_SUBMIT'
export const FORM_SUBMIT_SUCCESS = 'FORM_SUBMIT_SUCCESS'
export const FORM_SUBMIT_ERROR   = 'FORM_SUBMIT_ERROR'
export const FORM_UPDATE         = 'FORM_UPDATE'

export const actions = {}

actions.update = makeInstanceActionCreator(FORM_UPDATE, 'value')
actions.updateProp = makeInstanceActionCreator(FORM_UPDATE, 'path', 'value')
actions.setMode = makeInstanceActionCreator(FORM_SET_MODE, 'mode')
actions.setErrors = makeInstanceActionCreator(FORM_SET_ERRORS, 'errors')
actions.submit = makeInstanceActionCreator(FORM_SUBMIT)
actions.submitSuccess = makeInstanceActionCreator(FORM_SUBMIT_SUCCESS, 'updatedData')
actions.submitError = makeInstanceActionCreator(FORM_SUBMIT_ERROR, 'errors')

actions.load = makeInstanceActionCreator(FORM_LOAD, 'data')
actions.reset = (formName, data = {}, isNew = false) => ({
  type: makeInstanceAction(FORM_RESET, formName),
  data: data,
  isNew: isNew
})

actions.errors = (formName, errors) => (dispatch) => {
  const formErrors = {}
  if (errors && Array.isArray(errors)) {
    // read server errors and create a comprehensive object for the form
    errors.map(error => {
      const errorPath = error.path
        .replace(/^\/|\/$/g, '') // removes trailing and leading slashes
        .replace(/\//g, '.') // replaces / by . (for lodash)

      set(formErrors, errorPath, trans(error.message, {}, 'validators'))
    })

    // dispatch an error action if the caller want to do something particular
    dispatch(actions.submitError(formName, formErrors))

    // inject errors in form
    dispatch(actions.setErrors(formName, formErrors))
  }
}

actions.save = (formName, target) => (dispatch, getState) => {
  const formNew = formSelect.isNew(formSelect.form(getState(), formName))
  const formData = formSelect.data(formSelect.form(getState(), formName))
  const formErrors = formSelect.errors(formSelect.form(getState(), formName))

  dispatch(actions.submit(formName))

  if (!isEmpty(formErrors)) {
    const status = alertConstants.ALERT_STATUS_WARNING
    const action = formNew ? actionConstants.ACTION_CREATE:actionConstants.ACTION_UPDATE
    const alert = alertConstants.ALERT_ACTIONS[action][status]

    dispatch(alertActions.addAlert(
      formName+'validation',
      status,
      action,
      alert.title,
      alert.message
    ))

    return Promise.reject()
  }

  return dispatch({
    [API_REQUEST]: {
      url: target,
      request: {
        method: formNew ? 'POST' : 'PUT',
        body: JSON.stringify(formData)
      },
      success: (response, dispatch) => {
        dispatch(actions.submitSuccess(formName, response))

        if (response) {
          // I should check status code (204) instead but I don't have access to it here
          dispatch(actions.reset(formName, response, false))
        }
      },
      error: (errors, status, dispatch) => dispatch(actions.errors(formName, errors))
    }
  })
}

actions.cancelChanges = (formName) => (dispatch, getState) => {
  const formNew = formSelect.isNew(formSelect.form(getState(), formName))
  const originalData = formSelect.originalData(formSelect.form(getState(), formName))

  dispatch(actions.reset(formName, originalData, formNew))
}

// I keep them for retro compatibility.
// Please don't use them and use new naming
actions.resetForm = actions.reset
actions.saveForm = actions.save
