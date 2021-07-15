import {combineReducers} from '#/main/app/store/reducer'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store/reducer'

import {selectors} from '#/plugin/announcement/resources/announcement/modals/sending/store/selectors'

export const reducer = combineReducers({
  form: makeFormReducer(selectors.STORE_NAME+'.form'),
  receivers: makeListReducer(selectors.STORE_NAME+'.receivers')
})
