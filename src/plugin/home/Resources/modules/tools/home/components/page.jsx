import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool/containers/page'

import {Tab as TabTypes} from '#/plugin/home/prop-types'

const HomePage = props =>
  <ToolPage
    className="home-tool"
    path={props.breadcrumb}
    icon={props.currentTab && props.currentTab.icon ?
      <span className={`tool-icon fa fa-${props.currentTab.icon}`} /> : undefined
    }
    title={props.title}
    subtitle={props.subtitle}
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
  path: T.string,
  breadcrumb: T.array,
  title: T.string.isRequired,
  subtitle: T.string,
  poster: T.string,
  currentTab: T.shape(
    TabTypes.propTypes
  ),
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
