import {combineReducers, makeReducer} from '#/main/core/scaffolding/reducer'
import {makeFormReducer} from '#/main/core/data/form/reducer'

const reducer = makeFormReducer('editor', {}, {
  data: combineReducers({
    tabs: makeReducer([], {

    }),
    widgets: makeReducer([], {

    })
  })
})

export {
  reducer
}
