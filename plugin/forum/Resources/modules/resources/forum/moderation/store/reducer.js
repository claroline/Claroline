import {combineReducers} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'

import {select} from '#/plugin/forum/resources/forum/store/selectors'

const reducer = combineReducers({
  flaggedMessages: makeListReducer(`${select.STORE_NAME}.moderation.flaggedMessages`, {}),
  flaggedSubjects: makeListReducer(`${select.STORE_NAME}.moderation.flaggedSubjects`, {}),
  blockedMessages: makeListReducer(`${select.STORE_NAME}.moderation.blockedMessages`, {})
})


export {
  reducer
}
