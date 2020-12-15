import {combineReducers} from '#/main/app/store/reducer'

import {makeListReducer} from '#/main/app/content/list/store/reducer'

import {selectors} from '#/main/theme/appearance/store/selectors'

export const reducer = combineReducers({
  themes: makeListReducer(selectors.STORE_NAME+'.themes'),
  colors: makeListReducer(selectors.STORE_NAME+'.colors'),
  posters: makeListReducer(selectors.STORE_NAME+'.posters')
})
