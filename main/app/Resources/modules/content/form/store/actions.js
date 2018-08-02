import isEmpty from 'lodash/isEmpty'
import set from 'lodash/set'

import {makeInstanceAction, makeInstanceActionCreator} from '#/main/app/store/actions'

import {tval} from '#/main/core/translation'
import {API_REQUEST} from '#/main/app/api'
import {actions as alertActions} from '#/main/app/overlay/alert/store'
import {constants as alertConstants} from '#/main/app/overlay/alert/constants'
import {constants as actionConstants} from '#/main/app/action/constants'

import {selectors as formSelect} from '#/main/app/content/form/store/selectors'

export const FORM_RESET          = 'FORM_RESET'
export const FORM_SET_ERRORS     = 'FORM_SET_ERRORS'
export const FORM_SUBMIT         = 'FORM_SUBMIT'
export const FORM_SUBMIT_SUCCESS = 'FORM_SUBMIT_SUCCESS'
export const FORM_SUBMIT_ERROR   = 'FORM_SUBMIT_ERROR'
export const FORM_UPDATE         = 'FORM_UPDATE'

export const actions = {}

actions.update = makeInstanceActionCreator(FORM_UPDATE, 'value')
actions.updateProp = makeInstanceActionCreator(FORM_UPDATE, 'path', 'value')
actions.setErrors = makeInstanceActionCreator(FORM_SET_ERRORS, 'errors')
actions.submit = makeInstanceActionCreator(FORM_SUBMIT)
actions.submitSuccess = makeInstanceActionCreator(FORM_SUBMIT_SUCCESS, 'updatedData')
actions.submitError = makeInstanceActionCreator(FORM_SUBMIT_ERROR, 'errors')

actions.reset = (formName, data = {}, isNew = false) => ({
  type: makeInstanceAction(FORM_RESET, formName),
  data: data,
  isNew: isNew
})

actions.save = (formName, target) => (dispatch, getState) => {
  const formNew = formSelect.isNew(formSelect.form(getState(), formName))
  const formData = formSelect.data(formSelect.form(getState(), formName))
  const formErrors = formSelect.errors(formSelect.form(getState(), formName))

  dispatch(actions.submitForm(formName))

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
  } else {
    return dispatch({
      [API_REQUEST]: {
        url: target,
        request: {
          method: formNew ? 'POST' : 'PUT',
          body: JSON.stringify(formData)
        },
        success: (response, dispatch) => {
          dispatch(actions.submitFormSuccess(formName, response))
          dispatch(actions.resetForm(formName, response, false))
        },
        error: (errors, dispatch) => {
          // try to build form errors object from response
          const formErrors = {}
          if (errors && Array.isArray(errors)) {
            // read server errors and create a comprehensive object for the form
            errors.map(error => {
              const errorPath = error.path
                .replace(/^\/|\/$/g, '') // removes trailing and leading slashes
                .replace(/\//g, '.') // replaces / by . (for lodash)

              set(formErrors, errorPath, tval(error.message))
            })

            // dispatch an error action if the caller want to do something particular
            dispatch(actions.submitFormError(formName, formErrors))

            // inject errors in form
            dispatch(actions.setErrors(formName, formErrors))
          }
        }
      }
    })
  }
}

actions.cancelChanges = (formName) => (dispatch, getState) => {
  const formNew = formSelect.isNew(formSelect.form(getState(), formName))
  const originalData = formSelect.originalData(formSelect.form(getState(), formName))

  dispatch(actions.resetForm(formName, originalData, formNew))
}

// I keep them for retro compatibility.
// Please don't use them and use new naming
// TODO : remove me
actions.submitForm = actions.submit
actions.submitFormSuccess = actions.submitSuccess
actions.submitFormError = actions.submitError
actions.resetForm = actions.reset
actions.saveForm = actions.save