import {combineReducers} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/core/data/list/reducer'

const reducer = combineReducers({
  flaggedMessages: makeListReducer('moderation.flaggedMessages', {}),
  flaggedSubjects: makeListReducer('moderation.flaggedSubjects', {}),
  blockedMessages: makeListReducer('moderation.blockedMessages', {})
})


export {
  reducer
}
