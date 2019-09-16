import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {MenuSection} from '#/main/app/layout/menu/components/section'

const PlannedNotificationMenu = (props) =>
  <MenuSection
    {...omit(props, 'path')}
    title={trans('claroline_planned_notification_tool', {}, 'tools')}
  >
    <Toolbar
      className="list-group"
      buttonName="list-group-item"
      actions={[
        {
          name: 'notifications',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-bell',
          label: trans('notifications'),
          target: props.path+'/notifications'
        }, {
          name: 'messages',
          icon: 'fa fa-fw fa-envelope',
          type: LINK_BUTTON,
          label: trans('messages'),
          target: props.path+'/messages'
        }
      ]}
    />
  </MenuSection>

PlannedNotificationMenu.propTypes = {
  path: T.string
}

export {
  PlannedNotificationMenu
}
