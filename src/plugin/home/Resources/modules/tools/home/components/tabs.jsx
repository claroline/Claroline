import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {MENU_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {ToolMenu} from '#/main/core/tool/containers/menu'

import {Tab as TabTypes} from '#/plugin/home/prop-types'

const HomeTabs = (props) => {
  function getTabSummary(tab) {
    const children = get(tab, 'children', [])
      .filter(subTab => props.showHidden || !get(subTab, 'restrictions.hidden', false))

    if (isEmpty(children)) {
      return {
        type: LINK_BUTTON,
        icon: tab.icon ? `fa fa-fw fa-${tab.icon}` : undefined,
        label: tab.title,
        target: `${props.path}/${tab.slug}`
      }
    }

    return {
      type: MENU_BUTTON,
      icon: tab.icon ? `fa fa-fw fa-${tab.icon}` : undefined,
      label: tab.title,
      target: `${props.path}/${tab.slug}`,
      menu: {
        align: 'right',
        items: children.map(getTabSummary)
      }
    }
  }

  const tabs = props.tabs
    .filter(tab => props.showHidden || !get(tab, 'restrictions.hidden', false))

  return (
    <ToolMenu
      actions={(1 < tabs.length ? tabs.map(getTabSummary) : []).concat(props.actions)}
    />
  )
}

HomeTabs.propTypes = {
  path: T.string.isRequired,
  tabs: T.arrayOf(T.shape(
    TabTypes.propTypes
  )),
  actions: T.arrayOf(T.shape({
    // action types
  })),
  //currentTabId: T.string,
  showHidden: T.bool
}

HomeTabs.defaultProps = {
  showHidden: false,
  tabs: [],
  actions: []
}

export {
  HomeTabs
}
