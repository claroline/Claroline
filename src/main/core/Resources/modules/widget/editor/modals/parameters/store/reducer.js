import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {Widget as WidgetTypes} from '#/main/core/widget/prop-types'
import {selectors} from '#/main/core/widget/editor/modals/parameters/store/selectors'

const reducer = makeFormReducer(selectors.STORE_NAME, {
  data: WidgetTypes.defaultProps
})

export {
  reducer
}
