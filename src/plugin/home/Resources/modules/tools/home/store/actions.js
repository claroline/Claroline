import {API_REQUEST} from '#/main/app/api'
import {makeActionCreator} from '#/main/app/store/actions'

const CURRENT_TAB = 'CURRENT_TAB'
const ADMINISTRATION_SET = 'ADMINISTRATION_SET'
const TABS_LOAD = 'TABS_LOAD'

const actions = {}

actions.setCurrentTab = makeActionCreator(CURRENT_TAB, 'tab')
actions.setAdministration = makeActionCreator(ADMINISTRATION_SET, 'administration')
actions.loadTabs = makeActionCreator(TABS_LOAD, 'context', 'tabs')

actions.fetchTabs = (context, administration) => ({
  [API_REQUEST]: {
    url: [administration ? 'apiv2_home_admin_fetch' : 'apiv2_home_user_fetch'],
    request: {
      method: 'GET'
    },
    success: (data, dispatch) => {
      dispatch(actions.loadTabs(context, data))
    }
  }
})

export {
  actions,
  CURRENT_TAB,
  ADMINISTRATION_SET,
  TABS_LOAD
}