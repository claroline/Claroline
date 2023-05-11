import {makeReducer} from '#/main/app/store/reducer'
import {selectors} from './selectors'
import {makeInstanceAction} from '#/main/app/store/actions'
import {TOOL_LOAD} from '#/main/core/tool/store'

let initialeState = null
let handlers = {}
// eslint-disable-next-line no-unused-vars
const reducer = makeReducer(initialeState = null, handlers = {})

export {
  reducer
}
