import cloneDeep from 'lodash/cloneDeep'
import get from 'lodash/get'
import merge from 'lodash/merge'

import {actions as formActions} from '#/main/app/content/form/store/actions'

import {getFormDataPart, getTabParent, getTabPath} from '#/plugin/home/tools/home/editor/utils'
import {selectors} from '#/plugin/home/tools/home/editor/store/selectors'

// action creators
export const actions = {}

function pushTab(tab, tabs, position) {
  const newTabs = cloneDeep(tabs)

  switch (position.order) {
    case 'first':
      newTabs.unshift(tab)
      break

    case 'before':
    case 'after':
      if ('before' === position.order) {
        newTabs.splice(tabs.findIndex(t => t.id === position.tab), 0, tab)
      } else {
        newTabs.splice(tabs.findIndex(t => t.id === position.tab) + 1, 0, tab)
      }
      break

    case 'last':
      newTabs.push(tab)
      break
  }

  return newTabs
    // recompute tabs positions
    .map((tab, index) => merge({}, tab, {
      position: index + 1
    }))
}

actions.moveTab = (tabs, id, position) => {
  let newTabs = cloneDeep(tabs)

  // get the tab to move
  const original = get({tabs: newTabs}, getFormDataPart(id, newTabs))

  // remove the tab from its current position
  const parent = getTabParent(id, newTabs)
  if (parent) {
    const currentPos = parent.children.findIndex(child => child.id === id)
    parent.children.splice(currentPos, 1)
  } else {
    const currentPos = tabs.findIndex(child => child.id === id)
    newTabs.splice(currentPos, 1)
  }

  // move the tab at the new position
  if (position.parent) {
    const parent = get({tabs: newTabs}, getFormDataPart(position.parent, newTabs))

    parent.children = pushTab(original, parent.children, position)
  } else {
    newTabs = pushTab(original, newTabs, position)
  }

  // inject updated data into the form
  return formActions.updateProp(selectors.FORM_NAME, 'tabs', newTabs
    // recalculate tabs positions
    .sort((a, b) => a.position - b.position)
    .map((tab, index) => merge({}, tab, {
      position: index + 1
    }))
  )
}

actions.createTab = (parent = null, tab) => (dispatch, getState) => {
  const tabs = selectors.tabs(getState())
  if (parent) {
    const formData = selectors.formData(getState())

    const tabPath = `${getFormDataPart(parent.id, tabs)}.children`
    const children = get(formData, tabPath, [])

    return dispatch(formActions.updateProp(selectors.FORM_NAME, `${getFormDataPart(parent.id, tabs)}.children`, [].concat(children, [tab])))
  }

  return dispatch(formActions.updateProp(selectors.FORM_NAME, `tabs[${tabs.length}]`, tab))
}

actions.updateTab = (tabs, tabId, data, path) => {
  const tabPath = getFormDataPart(tabId, tabs)
  if (path) {
    return formActions.updateProp(selectors.FORM_NAME, `${tabPath}.${path}`, data)
  }

  return formActions.updateProp(selectors.FORM_NAME, tabPath, data)
}

actions.deleteTab = (tabs, tabToDelete) => {
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
  return formActions.updateProp(selectors.FORM_NAME, 'tabs', newTabs
    // recalculate tabs positions
    .sort((a, b) => a.position - b.position)
    .map((tab, index) => merge({}, tab, {
      position: index + 1
    }))
  )
}
