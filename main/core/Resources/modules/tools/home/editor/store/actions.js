import cloneDeep from 'lodash/cloneDeep'
import merge from 'lodash/merge'

import {trans} from '#/main/app/intl/translation'
import {makeId} from '#/main/core/scaffolding/id'

import {actions as formActions} from '#/main/app/content/form/store/actions'

import {selectors} from '#/main/core/tools/home/editor/store/selectors'
import {Tab as TabTypes} from '#/main/core/tools/home/prop-types'

// action creators
export const actions = {}

actions.createTab = (context, administration, position, currentUser, navigate) => (dispatch) => {
  const newTabId = makeId()
  const newSlug = 'new' + newTabId

  dispatch(formActions.updateProp(selectors.FORM_NAME, `[${position}]`, merge({}, TabTypes.defaultProps, {
    id: newTabId,
    title: trans('tab'),
    longTitle: trans('tab'),
    position: position + 1,
    slug: newSlug,
    type: administration ?
      'desktop' === context.type ? 'administration' : 'admin' :
      context.type,
    administration: administration,
    user: context.type === 'desktop' && !administration ? currentUser : null,
    workspace: context.type === 'workspace' ? {uuid: context.data.uuid} : null
  })))

  // open new tab
  navigate(`/edit/${newSlug}`)
}

actions.deleteTab = (tabs, tabToDelete) => (dispatch) => {
  const tabIndex = tabs.findIndex(tab => tab.id === tabToDelete.id)

  // creates a copy of the tabs list
  const newTabs = tabs.slice(0)

  // removes the tab to delete
  newTabs.splice(tabIndex, 1)

  // inject updated data into the form
  dispatch(formActions.update(selectors.FORM_NAME, newTabs
    // recalculate tabs positions
    .sort((a,b) => a.position - b.position)
    .map((tab, index) => merge({}, tab, {
      position: index + 1
    }))
  ))
}

actions.moveTab = (tabs, tabToMove, newPosition) => (dispatch) => {
  let newTabs = cloneDeep(tabs)

  const tabIndex = tabs.findIndex(tab => tab.id === tabToMove.id)
  newTabs.splice(tabIndex, 1)

  // if moving to the right
  if (tabToMove.position < newPosition) {
    newTabs = newTabs
      .sort((a,b) => a.position - b.position)
      .map((tab, index) => merge({}, tab, {
        position: index + 1
      }))
  }

  dispatch(formActions.update(selectors.FORM_NAME, [merge({}, tabToMove, {position: newPosition})]
    .concat(newTabs)
    .sort((a,b) => a.position - b.position)
    .map((tab, index) => merge({}, tab, {
      position: index + 1
    }))
  ))
}
