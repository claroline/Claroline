import {makeReducer} from '#/main/core/scaffolding/reducer'
import {makeResourceReducer} from '#/main/core/resource/reducer'

const reducer = makeResourceReducer({}, {
  textFile: makeReducer({}, {}),
  isHtml: makeReducer(false, {}),
  content: makeReducer('', {})
})

export {
  reducer
}