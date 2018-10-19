import {combineReducers} from '#/main/app/store/reducer'
import {reducer as logReducer} from '#/main/core/workspace/creation/components/log/reducer'


const reducer = combineReducers({
  log: logReducer
})

export {
  reducer
}
