import {makeActionCreator} from '#/main/app/store/actions'
import {API_REQUEST} from '#/main/app/api'

// actions
export const TAB_LOAD                 = 'TAB_LOAD'
export const TAB_SET_LOADED           = 'TAB_SET_LOADED'
export const TAB_RESTRICTIONS_DISMISS = 'TAB_RESTRICTIONS_DISMISS'

// action creators
export const actions = {}

actions.setTabLoaded = makeActionCreator(TAB_SET_LOADED, 'loaded')
actions.loadTab = makeActionCreator(TAB_LOAD, 'homeTab', 'managed', 'accessErrors')

actions.fetchTab = (id) => (dispatch) => dispatch({
  [API_REQUEST]: {
    silent: true,
    url: ['claro_home_tab_open', {id: id}],
    before: () => dispatch(actions.setTabLoaded(false)),
    success: (response) => dispatch(actions.loadTab(response.homeTab, response.managed, response.accessErrors || [])),
    error: (response, status) => {
      switch (status) {
        case 401:
        case 403:
          dispatch(actions.loadTab(response.homeTab, response.managed, response.accessErrors || []))
          break
      }
    }
  }
})

actions.dismissRestrictions = makeActionCreator(TAB_RESTRICTIONS_DISMISS)

actions.checkAccessCode = (tab, code) => (dispatch) => dispatch({
  [API_REQUEST] : {
    url: ['claro_home_tab_unlock', {id: tab.id}],
    request: {
      method: 'POST',
      body: JSON.stringify({code: code})
    },
    success: () => dispatch(actions.setTabLoaded(false)) // force reload the tab
  }
})
