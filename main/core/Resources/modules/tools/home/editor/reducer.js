
import {makeFormReducer} from '#/main/core/data/form/reducer'
import {makeReducer} from '#/main/app/store/reducer'


const reducer = makeFormReducer('editor', {
  data: makeReducer([] ,{})
})

export {
  reducer
}
