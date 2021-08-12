import {combineReducers} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'

import {selectors} from '#/plugin/cursus/tools/trainings/subscription/store/selectors'

export const reducer = combineReducers({
	subscriptions: makeListReducer(selectors.LIST_NAME)
})
