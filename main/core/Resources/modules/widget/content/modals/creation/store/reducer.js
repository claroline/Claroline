import {makeReducer, combineReducers} from '#/main/app/store/reducer'

import {makeFormReducer} from '#/main/core/data/form/reducer'

import {WidgetInstance as WidgetInstanceTypes} from '#/main/core/widget/content/prop-types'
import {selectors} from '#/main/core/widget/content/modals/creation/store/selectors'
import {WIDGET_CONTENTS_LOAD} from '#/main/core/widget/content/modals/creation/store/actions'

const reducer = combineReducers({
  availableTypes: makeReducer([], {
    [WIDGET_CONTENTS_LOAD]: (state, action) => action.types
  }),
  instance: makeFormReducer(selectors.FORM_NAME, {
    data: WidgetInstanceTypes.defaultProps
  })
})

export {
  reducer
}
