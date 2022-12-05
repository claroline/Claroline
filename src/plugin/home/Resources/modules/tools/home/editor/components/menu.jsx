import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {matchPath} from '#/main/app/router'
import {trans} from '#/main/app/intl/translation'
import {ContentSummary} from '#/main/app/content/components/summary'
import {CALLBACK_BUTTON, LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'

import {flattenTabs} from '#/plugin/home/tools/home/utils'
import {Tab as TabTypes} from '#/plugin/home/prop-types'
import {MODAL_HOME_CREATION} from '#/plugin/home/tools/home/editor/modals/creation'
import {MODAL_HOME_PARAMETERS} from '#/plugin/home/tools/home/editor/modals/parameters'
import {MODAL_HOME_POSITION} from '#/plugin/home/tools/home/editor/modals/position'

const EditorMenu = (props) => {
  function getTabSummary(tab, level = 0) {
    return {
      type: LINK_BUTTON,
      icon: tab.icon ? `fa fa-fw fa-${tab.icon}` : undefined,
      label: tab.title,
      target: `${props.path}/edit/${tab.slug}`,
      active: !!matchPath(props.location.pathname, {path: `${props.path}/edit/${tab.slug}`}),
      activeStyle: {
        borderColor: get(tab, 'display.color')
      },
      onClick: props.autoClose,
      additional: [
        {
          name: 'add',
          type: MODAL_BUTTON,
          icon: 'fa fa-fw fa-plus',
          label: trans('tab_add_child', {}, 'home'),
          disabled: 'administration' === tab.context && !props.administration,
          displayed: 1 > level, // only allow one sub-level of tabs
          modal: [MODAL_HOME_CREATION, {
            position: props.tabs.length,
            create: (newTab) => {
              props.createTab(tab, newTab, (slug) => props.history.push(`${props.path}/edit/${slug}`))
            }
          }],
          onClick: props.autoClose,
          group: trans('management')
        }, {
          name: 'configure',
          type: MODAL_BUTTON,
          icon: 'fa fa-fw fa-cog',
          label: trans('configure', {}, 'actions'),
          disabled: 'administration' === tab.context && !props.administration,
          modal: [MODAL_HOME_PARAMETERS, {
            tab: tab,
            save: (tab) => props.updateTab(props.tabs, tab.id, tab)
          }],
          onClick: props.autoClose,
          group: trans('management')
        }, {
          name: 'move',
          type: MODAL_BUTTON,
          icon: 'fa fa-fw fa-arrows',
          label: trans('move', {}, 'actions'),
          disabled: 'administration' === tab.context && !props.administration,
          modal: [MODAL_HOME_POSITION, {
            tab: tab,
            tabs: flattenTabs(props.tabs),
            selectAction: (position) => ({
              type: CALLBACK_BUTTON,
              label: trans('move', {}, 'actions'),
              callback: () => props.moveTab(tab.id, position)
            })
          }],
          onClick: props.autoClose,
          group: trans('management')
        }, {
          name: 'delete',
          type: CALLBACK_BUTTON,
          label: trans('delete', {}, 'actions'),
          icon: 'fa fa-fw fa-trash',
          dangerous: true,
          confirm: {
            title: trans('home_tab_delete_confirm_title', {}, 'home'),
            message: trans('home_tab_delete_confirm_message', {}, 'home'),
            subtitle: tab.title
          },
          disabled: ('administration' === tab.context && !props.administration) || 1 >= props.tabs.length,
          callback: () => props.deleteTab(props.tabs, tab),
          group: trans('management')
        }
      ],
      children: tab.children ? tab.children.map((child) => getTabSummary(child, level + 1)) : []
    }
  }

  return (
    <ContentSummary
      links={props.tabs.map(tab => getTabSummary(tab, 0)).concat([{
        type: MODAL_BUTTON,
        icon: 'fa fa-fw fa-plus',
        label: trans('add_tab', {}, 'home'),
        modal: [MODAL_HOME_CREATION, {
          position: props.tabs.length,
          create: (tab) => {
            props.createTab(null, tab, (slug) => props.history.push(`${props.path}/edit/${slug}`))
          }
        }],
        onClick: (e) => {
          props.autoClose(e)
        }
      }])}
    />
  )
}

EditorMenu.propTypes = {
  path: T.string,
  history: T.shape({
    push: T.func.isRequired
  }).isRequired,
  location: T.shape({
    pathname: T.string
  }).isRequired,
  tabs: T.arrayOf(T.shape(
    TabTypes.propTypes
  )),
  currentContext: T.shape({
    type: T.string.isRequired,
    data: T.object
  }),
  administration: T.bool,
  currentUser: T.object,
  autoClose: T.func.isRequired,
  createTab: T.func.isRequired,
  updateTab: T.func.isRequired,
  moveTab: T.func.isRequired,
  deleteTab: T.func.isRequired
}

EditorMenu.defaultProps = {
  tabs: []
}

export {
  EditorMenu
}
