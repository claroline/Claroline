import cloneDeep from 'lodash/cloneDeep'
import merge from 'lodash/merge'

import {actions as formActions} from '#/main/core/data/form/actions'

// action creators
export const actions = {}

actions.deleteTab = (currentTabIndex, editorTabs, push) => (dispatch) => {
  const newTabs = editorTabs.slice(0)
  const tabs = newTabs.splice(currentTabIndex, 1)
  // updating tabs positions
    .sort((a,b) => a.position - b.position)
    .map((tab, index) => merge({}, tab, {
      position: index + 1
    }))
  dispatch(formActions.updateProp('editor', 'tabs', tabs))
  push('/edit')
}

actions.updateTab = (editorTabs, updatedTab, oldTab) => (dispatch) => {

  let newTabs = cloneDeep(editorTabs)

  const oldTabIndex = editorTabs.findIndex(tab => oldTab.id === tab.id)
  newTabs.splice(oldTabIndex, 1)

  // if moving to the right
  if(updatedTab.position > oldTab.position) {
    newTabs = newTabs
      .sort((a,b) => a.position - b.position)
      .map((tab, index) => merge({}, tab, {
        position: index + 1
      }))
  }

  dispatch(formActions.updateProp('editor', 'tabs', [updatedTab]
    .concat(newTabs)
    .sort((a,b) => a.position - b.position)
    .map((tab, index) => merge({}, tab, {
      position: index + 1
    }))
  ))
}

actions.createTab = (editorTabs, formTab) => (dispatch) => dispatch(formActions.updateProp('editor', 'tabs', [formTab]
  .concat(editorTabs)
  .sort((a,b) => a.position - b.position)
  .map((tab, index) => merge({}, tab, {
    position: index + 1
  })) ))
