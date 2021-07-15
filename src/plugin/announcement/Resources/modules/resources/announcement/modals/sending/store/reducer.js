import {combineReducers} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'

import {selectors} from '#/plugin/announcement/resources/announcement/modals/sending/store/selectors'

export const reducer = combineReducers({
  receivers: makeListReducer(selectors.STORE_NAME+'.receivers')
})
