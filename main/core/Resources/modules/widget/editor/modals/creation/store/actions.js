import merge from 'lodash/merge'

import {makeId} from '#/main/core/scaffolding/id'

import {actions as formActions} from '#/main/core/data/form/actions'

import {WidgetContainer as WidgetContainerTypes} from '#/main/core/widget/prop-types'
import {selectors} from '#/main/core/widget/editor/modals/creation/store'

// action creators
export const actions = {}

actions.startCreation = (widgetLayout) => (dispatch) => {
  // initialize the form with default values
  dispatch(formActions.resetForm(selectors.STORE_NAME, merge({}, WidgetContainerTypes.defaultProps, {
    id: makeId()
  }), true))

  // set the widget layout
  // (I do it in 2 steps to let the form toggle the pending changes flag)
  dispatch(formActions.updateProp(selectors.STORE_NAME, 'display.layout', widgetLayout))
}
