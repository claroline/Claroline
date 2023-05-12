import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {selectors} from '#/main/theme/administration/appearance/modals/color-chart-edit/store/selectors'

const reducer = makeFormReducer(selectors.STORE_NAME, { new: true })

export {
  reducer
}
