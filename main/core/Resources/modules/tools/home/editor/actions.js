import cloneDeep from 'lodash/cloneDeep'
import merge from 'lodash/merge'

import {actions as formActions} from '#/main/core/data/form/actions'

// action creators
export const actions = {}

actions.deleteTab = (tabs, tabToDelete) => (dispatch) => {
  const tabIndex = tabs.findIndex(tab => tab.id === tabToDelete.id)

  // creates a copy of the tabs list
  const newTabs = tabs.slice(0)

  // removes the tab to delete
  newTabs.splice(tabIndex, 1)

  // inject updated data into the form
  dispatch(formActions.update('editor', newTabs
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

  dispatch(formActions.update('editor', [merge({}, tabToMove, {position: newPosition})]
    .concat(newTabs)
    .sort((a,b) => a.position - b.position)
    .map((tab, index) => merge({}, tab, {
      position: index + 1
    }))
  ))
}
