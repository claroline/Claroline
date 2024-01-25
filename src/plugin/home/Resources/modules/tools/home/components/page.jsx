import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool/containers/page'

import {getTabTitle} from '#/plugin/home/tools/home/utils'
import {Tab as TabTypes} from '#/plugin/home/prop-types'
import {HomeTabs} from '#/plugin/home/tools/home/components/tabs'
import classes from 'classnames'

const HomePage = props =>
  <ToolPage
    className="home-tool"
    path={[].concat(props.currentTab ? [{
      id: props.currentTab.id,
      type: LINK_BUTTON,
      label: getTabTitle(props.currentContext, props.currentTab),
      target: props.basePath+props.path+'/'+props.currentTab.slug
    }] : [], props.breadcrumb || [])}

    /*header={1 < props.tabs.length  ?
      <HomeTabs
        prefix={props.basePath+props.path}
        tabs={props.tabs}
        currentTabId={get(props.currentTab, 'id')}
        showSubMenu={props.showSubMenu}
        showHidden={props.showHidden}
      /> : undefined
    }*/
    nav={props.tabs
      .filter(tab => props.showHidden || !get(tab, 'restrictions.hidden', false))
      .map((tab) => ({
        key: tab.id,
        name: tab.id,
        type: LINK_BUTTON,
        target: `${props.basePath+props.path}/${tab.slug}`,
        icon: tab.icon ? `fa fa-fw fa-${tab.icon}` : undefined,
        label: tab.title,
      }))
    }
    icon={props.currentTab && props.currentTab.icon ?
      <span className={`tool-icon fa fa-${props.currentTab.icon}`} /> : undefined
    }
    title={props.title}
    subtitle={props.subtitle}
    showTitle={get(props.currentTab, 'display.showTitle')}
    poster={props.poster || get(props.currentTab, 'poster')}
    /*primaryAction="add"*/
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
  canEdit: T.bool.isRequired
}

HomePage.defaultProps = {
  path: '',
  actions: []
}

export {
  HomePage
}
