import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {PageFull} from '#/main/app/page'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {getToolBreadcrumb, showToolBreadcrumb} from '#/main/core/tool/utils'

import {Tab as TabTypes} from '#/plugin/home/prop-types'
import {Tabs} from '#/plugin/home/tools/home/components/tabs'

// {get(props.currentTab, 'display.centerTitle') ? 'text-center' : undefined}

const HomePage = props =>
  <PageFull
    className="home-tool"
    showBreadcrumb={showToolBreadcrumb(props.currentContext.type, props.currentContext.data)}
    path={[].concat(getToolBreadcrumb('home', props.currentContext.type, props.currentContext.data), props.currentTab ? [{
      id: props.currentTab.id,
      label: props.currentTabTitle,
      target: props.path+'/'+props.currentTab.slug
    }] : [])}
    meta={{
      title: `${trans('home', {}, 'tools')}${'workspace' === props.currentContext.type ? ' - ' + props.currentContext.data.code : ''}`,
      description: get(props.currentContext, 'data.meta.description')
    }}

    header={1 < props.tabs.length  ?
      <Tabs
        prefix={props.basePath+props.path}
        tabs={props.tabs}
        currentContext={props.currentContext}
      /> : undefined
    }
    icon={props.currentTab && props.currentTab.icon ?
      <span className={`tool-icon fa fa-${props.currentTab.icon}`} /> : undefined
    }
    title={props.currentTabTitle}
    poster={get(props.currentTab, 'poster.url')}
    toolbar="add | edit | fullscreen more"
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
  </PageFull>

HomePage.propTypes = {
  path: T.string,
  currentTabTitle: T.string.isRequired,
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
