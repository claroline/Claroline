import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {Tab as TabTypes} from '#/plugin/home/prop-types'
import {selectors} from '#/plugin/home/tools/home/editor/modals/creation/store/selectors'

const reducer = makeFormReducer(selectors.STORE_NAME, {
  data: TabTypes.defaultProps
})

export {
  reducer
}
