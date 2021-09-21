import {makeActionCreator} from '#/main/app/store/actions'
import {API_REQUEST} from '#/main/app/api'

import {actions as formActions} from '#/main/app/content/form/store/actions'

import {selectors} from '#/main/core/resource/modals/rights/store/selectors'

export const SET_RIGHTS_RECURSIVE = 'SET_RIGHTS_RECURSIVE'

export const actions = {}

actions.setRecursive = makeActionCreator(SET_RIGHTS_RECURSIVE, 'recursiveEnabled')

actions.fetchRights = (resourceNode) => (dispatch) => dispatch({
  [API_REQUEST]: {
    url: ['apiv2_resource_get_rights', {id: resourceNode.id}],
    silent: true,
    request: {
      method: 'GET'
    },
    success: (response) => dispatch(formActions.resetForm(selectors.FORM_NAME, Object.assign({}, resourceNode, {
      rights: response
    })))
  }
})
