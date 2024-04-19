import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import isEmpty from 'lodash/isEmpty'
import {LINK_BUTTON, MENU_BUTTON} from '#/main/app/buttons'

/**
 * Flattens a tree of tabs into a one-level array.
 *
 * @param {Array} tabs
 */
function flattenTabs(tabs) {
  function flatten(tab, parent = null) {
    const children = get(tab, 'children', []) || []
    const flatTab = Object.assign({}, tab)

    delete flatTab.children
    if (parent) {
      flatTab.parent = {
        id: parent.id,
        slug: parent.slug,
        title: parent.title
      }
    }

    let flattened = [flatTab]

    if (children) {
      children.map((child) => {
        flattened = flattened.concat(flatten(child, flatTab))
      })
    }

    return flattened
  }

  return tabs.reduce((acc, tab) => acc.concat(flatten(tab)), [])
}

function getTabTitle(context, tab) {
  if (tab) {
    return tab.longTitle
  }

  if (context.data && context.data.name) {
    return context.data.name
  }

  if ('desktop' === context.type) {
    return trans('desktop')
  }

  return trans('home')
}

function getTabSummary(path, tab, showHidden = false) {
  const children = get(tab, 'children', [])
    .filter(subTab => showHidden || !get(subTab, 'restrictions.hidden', false))

  if (isEmpty(children)) {
    return {
      type: LINK_BUTTON,
      icon: tab.icon ? `fa fa-fw fa-${tab.icon}` : undefined,
      label: tab.title,
      target: `${path}/${tab.slug}`
    }
  }

  return {
    type: MENU_BUTTON,
    icon: tab.icon ? `fa fa-fw fa-${tab.icon}` : undefined,
    label: tab.title,
    target: `${path}/${tab.slug}`,
    menu: {
      align: 'right',
      items: children.map((child) => getTabSummary(path, child, showHidden))
    }
  }
}

export {
  flattenTabs,
  getTabTitle,
  getTabSummary
}
