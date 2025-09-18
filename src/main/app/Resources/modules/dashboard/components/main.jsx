import React from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'
import omit from 'lodash/omit'

import {Routes} from '#/main/app/router'
import {Toolbar} from '#/main/app/action'
import {LINK_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl'

const Dashboard = (props) => {
  const pages = [
    {
      name: 'overview',
      icon: 'fa fa-temperature-half',
      title: trans('overview'),
      disabled: !props.overviewPage,
      component: props.overviewPage
    }
  ]
    .concat(props.pages || [])
    .concat([
      {
        name: 'stats',
        icon: 'fa fa-pie-chart',
        title: trans('statistics'),
        disabled: !props.statsPage,
        component: props.statsPage
      }, {
        name: 'activity',
        icon: 'fa fa-line-chart',
        title: trans('activity'),
        disabled: !props.activityPage,
        component: props.activityPage
      }, {
        name: 'actions',
        icon: 'fa fa-ellipsis-h',
        title: trans('more'),
        disabled: !props.actionsPage,
        component: props.actionsPage
      }
    ])
    .filter(page => !page.disabled)

  return (
    <>
      <Toolbar
        className="app-dashboard-toolbar m-4 me-0"
        buttonName="btn btn-text-body fs-sm app-dashboard-btn focus-ring px-3 py-4"
        actions={pages.map(page => ({
          name: page.name,
          type: LINK_BUTTON,
          icon: page.icon,
          label: page.title,
          target: props.path + '/' + (page.path || page.name),
          displayed: !page.disabled
        }))}
      />

      <Routes
        path={props.path}
        redirect={!isEmpty(pages) ? [
          {from: '/', exact: true, to: '/' + pages[0].name}
        ] : undefined}
        routes={pages.map(page => ({
          path: page.path || '/' + page.name,
          ...omit(page, 'name', 'icon', 'title')
        }))}
      />
    </>
  )
}

Dashboard.propTypes = {
  path: T.string.isRequired,
  pages: T.arrayOf(T.shape({
    name: T.string.isRequired,
    icon: T.string.isRequired,
    title: T.string.isRequired,
    component: T.elementType,
    render: T.func,
    disabled: T.bool
  })),

  // standard pages
  overviewPage: T.elementType,
  statsPage: T.elementType,
  activityPage: T.elementType,
  actionsPage: T.elementType
}

export {
  Dashboard
}
