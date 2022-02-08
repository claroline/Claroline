import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool/containers/page'

import {getTabTitle} from '#/plugin/home/tools/home/utils'
import {Tab as TabTypes} from '#/plugin/home/prop-types'
import {Tabs} from '#/plugin/home/tools/home/components/tabs'

const HomePage = props =>
  <ToolPage
    className="home-tool"
    path={[].concat(props.currentTab ? [{
      id: props.currentTab.id,
      type: LINK_BUTTON,
      label: getTabTitle(props.currentContext, props.currentTab),
      target: props.basePath+props.path+'/'+props.currentTab.slug
    }] : [], props.breadcrumb || [])}

    header={1 < props.tabs.length  ?
      <Tabs
        prefix={props.basePath+props.path}
        tabs={props.tabs}
        currentContext={props.currentContext}
        showSubMenu={props.showSubMenu}
        showHidden={props.showHidden}
      /> : undefined
    }
    icon={props.currentTab && props.currentTab.icon ?
      <span className={`tool-icon fa fa-${props.currentTab.icon}`} /> : undefined
    }
    title={props.title}
    subtitle={props.subtitle}
    showTitle={get(props.currentTab, 'display.showTitle')}
    poster={props.poster || get(props.currentTab, 'poster.url')}
    primaryAction="add"
    actions={props.currentTab ? [
      {
        name: 'edit',
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-pencil',
        label: trans('edit', {}, 'actions'),
        displayed: props.canEdit,
        primary: true,
        target: `${props.basePath}/edit/${props.currentTab.slug}`,
        group: trans('management')
      }, {
        name: 'switch-user',
        type: CALLBACK_BUTTON,
        icon: 'fa fa-fw fa-exchange',
        label: trans('switch_to_user_tabs'),
        displayed: 'desktop' === props.currentContext.type && props.canAdministrate && props.administration,
        dangerous: true,
        callback: () => {
          props.setAdministration(false)
          props.fetchTabs(props.currentContext, false)
        },
        group: trans('management')
      }, {
        name: 'switch-admin',
        type: CALLBACK_BUTTON,
        icon: 'fa fa-fw fa-exchange',
        label: trans('switch_to_admin_tabs'),
        displayed: 'desktop' === props.currentContext.type && props.canAdministrate && !props.administration,
        callback: () => {
          props.setAdministration(true)
          props.fetchTabs(props.currentContext, true)
        },
        group: trans('management')
      }
    ].concat(props.actions) : undefined}
  >
    {props.children}
  </ToolPage>

HomePage.propTypes = {
  showSubMenu: T.bool,
  showHidden: T.bool,
  path: T.string,
  breadcrumb: T.array,
  title: T.string.isRequired,
  subtitle: T.string,
  poster: T.string,
  currentTab: T.shape(
    TabTypes.propTypes
  ),
  tabs: T.arrayOf(T.shape(
    TabTypes.propTypes
  )),
  actions: T.arrayOf(T.shape({
    // action types
  })),
  children: T.any,

  // from store
  basePath: T.string.isRequired,
  currentContext: T.shape({
    type: T.string.isRequired,
    data: T.object
  }).isRequired,
  canEdit: T.bool.isRequired,
  canAdministrate: T.bool.isRequired,
  administration: T.bool.isRequired,
  setAdministration: T.func,
  fetchTabs: T.func
}

HomePage.defaultProps = {
  path: '',
  actions: []
}

export {
  HomePage
}
