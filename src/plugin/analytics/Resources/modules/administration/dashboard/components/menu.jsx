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
          name: 'activity',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-chart-line',
          label: trans('activity'),
          target: props.path + '/activity'
        }, {
          name: 'content',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-photo-video',
          label: trans('content'),
          target: props.path + '/content'
        }, {
          name: 'community',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-users',
          label: trans('community'),
          target: props.path + '/community'
        }
      ]}
      onClick={props.autoClose}
    />
  </MenuSection>

DashboardMenu.propTypes = {
  path: T.string,

  // from menu
  opened: T.bool.isRequired,
  toggle: T.func.isRequired,
  autoClose: T.func.isRequired
}

export {
  DashboardMenu
}
