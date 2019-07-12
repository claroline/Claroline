import {combineReducers} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'

import {selectors} from '#/plugin/forum/resources/forum/store/selectors'

const reducer = combineReducers({
  flaggedMessages: makeListReducer(`${selectors.STORE_NAME}.moderation.flaggedMessages`),
  flaggedSubjects: makeListReducer(`${selectors.STORE_NAME}.moderation.flaggedSubjects`),
  blockedMessages: makeListReducer(`${selectors.STORE_NAME}.moderation.blockedMessages`),
  blockedSubjects: makeListReducer(`${selectors.STORE_NAME}.moderation.blockedSubjects`)
})


export {
  reducer
}
