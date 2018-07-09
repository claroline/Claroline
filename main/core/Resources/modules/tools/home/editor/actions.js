import {makeActionCreator} from '#/main/core/scaffolding/actions'
import {API_REQUEST} from '#/main/core/api/actions'

export const UPDATE_DELETED_TABS = 'UPDATE_DELETED_TABS'
export const actions = {}


actions.updateDeletedTabs = makeActionCreator(UPDATE_DELETED_TABS, 'tabId')
actions.deleteTab = (tabId, push) => ({
  [API_REQUEST]: {
    url: ['apiv2_home_tab_delete_bulk', {ids: [tabId]}],
    request: {
      method: 'DELETE'
    },
    success: (data, dispatch) => {
      dispatch(actions.updateDeletedTabs(tabId))
      push('/')
    }
  }
})
