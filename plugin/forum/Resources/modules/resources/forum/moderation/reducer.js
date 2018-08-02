import {combineReducers} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'

const reducer = combineReducers({
  flaggedMessages: makeListReducer('moderation.flaggedMessages', {}),
  flaggedSubjects: makeListReducer('moderation.flaggedSubjects', {}),
  blockedMessages: makeListReducer('moderation.blockedMessages', {})
})


export {
  reducer
}
