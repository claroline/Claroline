import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'
import isEmpty from 'lodash/isEmpty'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {MenuSection} from '#/main/app/layout/menu/components/section'

import {getWorkspaceAnalytics} from '#/plugin/analytics/utils'

const DashboardMenu = (props) =>
  <MenuSection
    {...omit(props, 'path', 'workspace')}
    title={trans('dashboard', {}, 'tools')}
  >
    <Toolbar
      className="list-group"
      buttonName="list-group-item"
      actions={getWorkspaceAnalytics(props.workspace).then(apps => [
        {
          name: 'overview',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-pie-chart',
          label: trans('overview', {}, 'analytics'),
          target: props.path,
          exact: true
        }
      ].concat(apps
        .filter(app => !isEmpty(get(app, 'components.tab')))
        .map(app => ({
          name: app.name,
          type: LINK_BUTTON,
          icon: app.meta.icon,
          label: app.meta.label,
          target: `${props.path}/${app.name}`
        })))
      )}
      onClick={props.autoClose}
    />
  </MenuSection>

DashboardMenu.propTypes = {
  path: T.string,
  workspace: T.object,

  // from menu
  opened: T.bool.isRequired,
  toggle: T.func.isRequired,
  autoClose: T.func.isRequired
}

export {
  DashboardMenu
}
