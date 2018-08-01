import {combineReducers} from '#/main/core/scaffolding/reducer'
import {reducer as logReducer} from '#/main/core/workspace/creation/components/log/reducer'


const reducer = combineReducers({
  log: logReducer
})

export {
  reducer
}
