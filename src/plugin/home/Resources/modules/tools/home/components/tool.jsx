import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {matchPath} from '#/main/app/router'
import {trans} from '#/main/app/intl'
import {LINK_BUTTON, MENU_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {Tool} from '#/main/core/tool'

import {PlayerMain} from '#/plugin/home/tools/home/player/containers/main'
import {EditorMain} from '#/plugin/home/tools/home/editor/containers/main'
import {MODAL_HOME_CREATION} from '#/plugin/home/tools/home/editor/modals/creation'

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

const HomeTool = props => {
  const inEditor = matchPath(props.location.pathname, {path: `${props.path}/edit`})

  let tabs = []
  if (inEditor) {
    tabs = props.editorTabs.map((tab) => getTabSummary(`${props.path}/edit`, tab, true))
  } else {
    tabs = props.tabs
      .filter(tab => props.showHidden || !get(tab, 'restrictions.hidden', false))
      .map((tab) => getTabSummary(props.path, tab, false))
  }

  return (
    <Tool
      {...props}
      styles={['claroline-distribution-plugin-home-home-tool']}
      menu={(1 < tabs.length ? tabs : []).concat([
        {
          type: MODAL_BUTTON,
          icon: 'fa fa-fw fa-plus',
          label: trans('add_tab', {}, 'home'),
          displayed: inEditor,
          modal: [MODAL_HOME_CREATION, {
            position: props.editorTabs.length,
            create: (tab) => {
              props.createTab(null, tab, (slug) => props.history.push(`${props.path}/edit/${slug}`))
            }
          }]
        }
      ])}
      pages={[
        {
          path: '/edit',
          disabled: !props.canEdit,
          component: EditorMain
        }, {
          path: '/',
          component: PlayerMain
        }
      ]}
    />
  )
}

HomeTool.propTypes = {
  path: T.string.isRequired,
  tabs: T.array,
  editorTabs: T.array,
  canEdit: T.bool.isRequired,
  // from router
  history: T.shape({
    push: T.func.isRequired
  }).isRequired,
  location: T.shape({
    pathname: T.string.isRequired
  }).isRequired
}

HomeTool.defaultProps = {
  tabs: [],
  editorTabs: []
}

export {
  HomeTool
}
