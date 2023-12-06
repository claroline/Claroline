import merge from 'lodash/merge'

import {trans} from '#/main/app/intl/translation'
import {makeId} from '#/main/core/scaffolding/id'

import {actions as formActions} from '#/main/app/content/form/store/actions'

import {Tab as TabTypes} from '#/plugin/home/prop-types'
import {selectors} from '#/plugin/home/tools/home/editor/modals/creation/store/selectors'

// action creators
export const actions = {}

actions.startCreation = (type, position) => (dispatch) => {
  const newTabId = makeId()
  const newSlug = 'new' + newTabId

  // initialize the form with default values
  dispatch(formActions.resetForm(selectors.STORE_NAME, merge({}, TabTypes.defaultProps, {
    title: trans('tab'),
    longTitle: trans('tab'),
    position: position + 1,
    slug: newSlug,
    class: type.class,
    type: type.name,
    _new: true // this is used to avoid requesting an ObjectLock to the server as the tab not already exists
  }), true))

  // set the tab title
  // (I do it in 2 steps to let the form toggle the pending changes flag)
  dispatch(formActions.updateProp(selectors.STORE_NAME, 'id', newTabId))
}

actions.reset = () => formActions.resetForm(selectors.STORE_NAME, merge({}, TabTypes.defaultProps), true)
