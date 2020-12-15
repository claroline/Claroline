import cloneDeep from 'lodash/cloneDeep'
import get from 'lodash/get'
import merge from 'lodash/merge'

import {makeActionCreator} from '#/main/app/store/actions'
import {actions as formActions} from '#/main/app/content/form/store/actions'

import {getFormDataPart, getTabPath} from '#/plugin/home/tools/home/editor/utils'
import {selectors} from '#/plugin/home/tools/home/editor/store/selectors'

export const HOME_MOVE_TAB = 'HOME_MOVE_TAB'

// action creators
export const actions = {}

actions.moveTab = makeActionCreator(HOME_MOVE_TAB, 'id', 'position')

actions.createTab = (parent = null, tab, navigate) => (dispatch, getState) => {
  const tabs = selectors.editorTabs(getState())
  if (parent) {
    const tabPath = `${getFormDataPart(parent.id, tabs)}.children`
    const children = get(tabs, tabPath, [])

    dispatch(formActions.updateProp(selectors.FORM_NAME, `${getFormDataPart(parent.id, tabs)}.children`, [].concat(children, tab)))
  } else {
    dispatch(formActions.updateProp(selectors.FORM_NAME, `[${tabs.length}]`, tab))
  }


  // open new tab
  navigate(tab.slug)
}

actions.updateTab = (tabs, tabId, data, path) => {
  const tabPath = getFormDataPart(tabId, tabs)
  if (path) {
    return formActions.updateProp(selectors.FORM_NAME, `${tabPath}.${path}`, data)
  }

  return formActions.updateProp(selectors.FORM_NAME, tabPath, data)
}

actions.deleteTab = (tabs, tabToDelete) => (dispatch) => {
  const newTabs = cloneDeep(tabs)
  const tabPath = getTabPath(tabToDelete.id, newTabs)

  if (tabPath.length === 1) {
    newTabs.splice(tabPath[0], 1)
  } else {
    let tab = newTabs[tabPath[0]]

    for (let i = 1; i < tabPath.length - 1; ++i) {
      tab = tab.children[tabPath[i]]
    }
    tab.children.splice(tabPath[tabPath.length - 1], 1)
  }

  // inject updated data into the form
  dispatch(formActions.update(selectors.FORM_NAME, newTabs
    // recalculate tabs positions
    .sort((a, b) => a.position - b.position)
    .map((tab, index) => merge({}, tab, {
      position: index + 1
    }))
  ))
}