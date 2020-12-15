import {combineReducers} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'

import {selectors} from '#/plugin/audio-player/files/audio/store/selectors'

const reducer = combineReducers({
  comments: makeListReducer(selectors.STORE_NAME+'.comments')
})

export {
  reducer
}
