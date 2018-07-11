import {makeFormReducer} from '#/main/core/data/form/reducer'

import {Tab as TabTypes} from '#/main/core/tools/home/prop-types'
import {selectors} from '#/main/core/tools/home/editor/modals/parameters/store/selectors'

const reducer = makeFormReducer(selectors.STORE_NAME, {
  data: TabTypes.defaultProps
})

export {
  reducer
}
