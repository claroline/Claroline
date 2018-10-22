import {makeReducer} from '#/main/app/store/reducer'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'

const reducer = {
  tools: makeReducer(),
  toolsConfig: makeFormReducer('toolsConfig')
}

export {
  reducer
}