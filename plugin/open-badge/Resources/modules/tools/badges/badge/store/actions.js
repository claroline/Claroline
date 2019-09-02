import {tval} from '#/main/app/intl/translation'
import set from 'lodash/set'

import {url} from '#/main/app/api'

import {API_REQUEST} from '#/main/app/api'
import {actions as listActions} from '#/main/app/content/list/store'
import {actions as formActions} from '#/main/app/content/form/store'
import {selectors}  from '#/plugin/open-badge/tools/badges/store/selectors'

export const actions = {}

actions.enable = (badges) => ({
  [API_REQUEST]: {
    url: url(['apiv2_badge-class_enable'], {ids: badges.map(u => u.id)}),
    request: {
      method: 'PUT'
    },
    success: (data, dispatch) => {
      dispatch(listActions.invalidateData(selectors.STORE_NAME +'.badges.list'))
    }
  }
})

actions.disable = (badges) => ({
  [API_REQUEST]: {
    url: url(['apiv2_badge-class_disable'], {ids: badges.map(u => u.id)}),
    request: {
      method: 'PUT'
    },
    success: (data, dispatch) => {
      dispatch(listActions.invalidateData(selectors.STORE_NAME +'.badges.list'))
    }
  }
})

// TODO : this must use the standard form actions
actions.save = (formName, badge, workspace, isNew) => ({
  [API_REQUEST]: {
    url: isNew ? ['apiv2_badge-class_create']: ['apiv2_badge-class_update', {id: badge.id}],
    request: {
      body: JSON.stringify(Object.assign({}, badge, {workspace})),
      method: isNew ? 'POST': 'PUT'
    },
    success: (response, dispatch) => {
      dispatch(formActions.resetForm(formName, response, false))
      dispatch(listActions.invalidateData(selectors.STORE_NAME +'.badges.list'))
    },
    error: (errors, status, dispatch) => {
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
        dispatch(formActions.submitError(formName, formErrors))

        // inject errors in form
        dispatch(formActions.setErrors(formName, formErrors))
      }
    }
  }
})
