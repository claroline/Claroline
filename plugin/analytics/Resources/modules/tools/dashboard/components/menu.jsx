import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {MenuSection} from '#/main/app/layout/menu/components/section'

const DashboardMenu = (props) =>
  <MenuSection
    {...omit(props, 'path')}
    title={trans('dashboard', {}, 'tools')}
  >
    <Toolbar
      className="list-group"
      buttonName="list-group-item"
      actions={[
        {
          name: 'overview',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-pie-chart',
          label: trans('overview', {}, 'analytics'),
          target: props.path,
          exact: true
        }, {
          name: 'connections',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-clock',
          label: trans('connection_time'),
          target: `${props.path}/connections`
        }, {
          name: 'log',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-users',
          label: trans('users_actions'),
          target: `${props.path}/log`
        }, {
          name: 'logs_users',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-user',
          label: trans('user_actions'),
          target: `${props.path}/logs/users`
        }, {
          name: 'progression',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-tasks',
          label: trans('progression'),
          target: `${props.path}/progression`
        }, {
          name: 'paths',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-wave-square',
          label: trans('paths_tracking'),
          target: `${props.path}/paths`
        }, {
          name: 'evaluations',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-graduation-cap',
          label: trans('evaluations', {}, 'analytics'),
          target: `${props.path}/evaluations`
        }, {
          name: 'requirements',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-clipboard-list',
          label: trans('evaluation_requirements', {}, 'analytics'),
          target: `${props.path}/requirements`
        }
      ]}
    />
  </MenuSection>

DashboardMenu.propTypes = {
  path: T.string
}

export {
  DashboardMenu
}
