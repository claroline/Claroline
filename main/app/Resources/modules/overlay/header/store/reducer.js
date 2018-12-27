import {makeReducer} from '#/main/app/store/reducer'

// TODO : this should be moved in the the main app store when available

const reducer = {
  isImpersonated: makeReducer(null),
  mainMenu: makeReducer(null),
  context: makeReducer({}),
  display: makeReducer({}),
  tools: makeReducer([]),
  userTools: makeReducer([]),
  notificationTools: makeReducer([]),
  administration: makeReducer([])
}

export {
  reducer
}
