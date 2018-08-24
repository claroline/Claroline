import merge from 'lodash/merge'

import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {makeId} from '#/main/core/scaffolding/id'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {WidgetInstance as WidgetInstanceTypes} from '#/main/core/widget/content/prop-types'
import {selectors} from '#/main/core/widget/content/modals/creation/store/selectors'
import {WIDGET_CONTENTS_LOAD, SET_MODAL_STEP} from '#/main/core/widget/content/modals/creation/store/actions'

const reducer = combineReducers({
  currentStep: makeReducer('widget', {
    [SET_MODAL_STEP]: (state, action) => action.stepName
  }),
  widgets: makeReducer([], {
    [WIDGET_CONTENTS_LOAD]: (state, action) => action.widgets
  }),
  dataSources: makeReducer([], {
    [WIDGET_CONTENTS_LOAD]: (state, action) => action.dataSources
  }),
  instance: makeFormReducer(selectors.FORM_NAME, {
    data: merge({id: makeId()}, WidgetInstanceTypes.defaultProps)
  })
})

export {
  reducer
}
