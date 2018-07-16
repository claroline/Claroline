
import {makeFormReducer} from '#/main/core/data/form/reducer'
import {makeReducer} from '#/main/core/scaffolding/reducer'


const reducer = makeFormReducer('editor', {
  data: makeReducer([] ,{})
})

export {
  reducer
}
