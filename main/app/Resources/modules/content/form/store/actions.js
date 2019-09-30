import isEmpty from 'lodash/isEmpty'
import set from 'lodash/set'

import {makeInstanceAction, makeInstanceActionCreator} from '#/main/app/store/actions'

import {dateToDisplayFormat} from '#/main/app/intl/date'
import {trans, tval} from '#/main/app/intl/translation'
import {API_REQUEST} from '#/main/app/api'
import {actions as alertActions} from '#/main/app/overlays/alert/store'
import {constants as alertConstants} from '#/main/app/overlays/alert/constants'
import {constants as actionConstants} from '#/main/app/action/constants'
import {MODAL_CONFIRM} from '#/main/app/modals/confirm'
import {actions as modalActions} from '#/main/app/overlays/modal/store'
import {selectors as securitySelectors} from '#/main/app/security/store/selectors'
import {selectors as formSelect} from '#/main/app/content/form/store/selectors'

export const FORM_RESET          = 'FORM_RESET'
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

actions.reset = (formName, data = {}, isNew = false) => ({
  type: makeInstanceAction(FORM_RESET, formName),
  data: data,
  isNew: isNew
})

//the dispatch returned in the success function isn't the same as the first one
//async request doesn't work with the usual way otherwise
actions.getItemLock = (className, id) => (dispatch) => {
  if (id) {
    dispatch({
      [API_REQUEST]: {
        url: ['apiv2_object_lock_get', {class: className, id}],
        request: {
          method: 'GET'
        },
        success: (response) => {
          if (response.value) {
            return dispatch(actions.validateLock(response, className, id))
          }

          dispatch(actions.lockItem(className, id))
        }
      }
    })
  } else {
    //something went wrong (ie removed element)
  }
}

actions.validateLock = (lock) => (dispatch, getState) => {
  const currentUser = securitySelectors.currentUser(getState())

  if (lock.user.username !== currentUser.username) {
    dispatch(
      modalActions.showModal(MODAL_CONFIRM, {
        title: trans('update_object'),
        dangerous: true,
        icon: 'fa fa-fw fa-check',
        question: trans('object_currently_modified', {username: lock.user.username, date: dateToDisplayFormat(lock.updated)}),
        confirmButtonText: trans('update_anyway'),
        handleConfirm: () => {
          dispatch(actions.lockItem(lock.className, lock.id))
        }
      })
    )
  }
}

actions.lockItem = (className, id) => ({
  [API_REQUEST]: {
    url: ['apiv2_object_lock', {class: className, id}],
    request: {
      method: 'PUT'
    }
  }
})

actions.unlockItem = (className, id) => ({
  [API_REQUEST]: {
    url: ['apiv2_object_unlock', {class: className, id}],
    request: {
      method: 'PUT'
    }
  }
})

actions.errors = (formName, errors) => (dispatch) => {
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
// TODO : remove me
actions.resetForm = actions.reset
actions.saveForm = actions.save
