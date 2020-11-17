import merge from 'lodash/merge'

import {makeActionCreator} from '#/main/app/store/actions'
import {actions as formActions} from '#/main/app/content/form/store/actions'

import {selectors} from '#/plugin/home/tools/home/editor/store/selectors'

export const HOME_MOVE_TAB = 'HOME_MOVE_TAB'

// action creators
export const actions = {}

actions.moveTab = makeActionCreator(HOME_MOVE_TAB, 'id', 'position')

actions.createTab = (index, tab, navigate) => (dispatch) => {
  dispatch(formActions.updateProp(selectors.FORM_NAME, `[${index}]`, tab))

  // open new tab
  navigate(tab.slug)
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
    .sort((a, b) => a.position - b.position)
    .map((tab, index) => merge({}, tab, {
      position: index + 1
    }))
  ))
}
