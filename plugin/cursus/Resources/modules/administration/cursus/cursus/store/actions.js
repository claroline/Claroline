import cloneDeep from 'lodash/cloneDeep'
import set from 'lodash/set'

import {API_REQUEST, url} from '#/main/app/api'
import {actions as formActions} from '#/main/app/content/form/store'
import {actions as listActions} from '#/main/app/content/list/store'

export const actions = {}

actions.open = (formName, defaultProps, id = null, parentId = null) => (dispatch) => {
  if (id) {
    dispatch({
      [API_REQUEST]: {
        url: ['apiv2_cursus_get', {id}],
        success: (response, dispatch) => {
          dispatch(formActions.resetForm(formName, response, false))
        }
      }
    })
  } else if (parentId) {
    dispatch({
      [API_REQUEST]: {
        url: ['apiv2_cursus_get', {id: parentId}],
        success: (response, dispatch) => {
          const cursusProps = cloneDeep(defaultProps)
          set(cursusProps, 'parent', response)
          dispatch(formActions.resetForm(formName, cursusProps, true))
        }
      }
    })
  } else {
    dispatch(formActions.resetForm(formName, defaultProps, true))
    dispatch(listActions.invalidateData(formName+'.organizations.list'))
  }
}

actions.reset = (formName) => (dispatch) => {
  dispatch(formActions.resetForm(formName, {}, true))
}

actions.addOrganizations = (cursusId, organizations) => ({
  [API_REQUEST]: {
    url: url(['apiv2_cursus_add_organizations', {id: cursusId}], {ids: organizations}),
    request: {
      method: 'PATCH'
    },
    success: (data, dispatch) => {
      dispatch(listActions.invalidateData('cursus.current.organizations.list'))
    }
  }
})
