import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {trans} from '#/main/core/translation'
import {Button} from '#/main/app/action/components/button'
import {URL_BUTTON, MENU_BUTTON} from '#/main/app/buttons'

const NotificationsMenu = () =>
  <ul className="app-notifications dropdown-menu dropdown-menu-right">
    <li role="presentation">
      <Button
        type={URL_BUTTON}
        icon="fa fa-fw fa-bell"
        label={trans('show-notifications', {}, 'actions')}
        target={['icap_notification_view']}
      />
    </li>
  </ul>

NotificationsMenu.propTypes = {

}

const HeaderNotifications = props =>
  <Button
    id="app-notifications-menu"
    type={MENU_BUTTON}
    className="app-header-item app-header-btn"
    icon={classes('fa fa-fw', {
      'fa-mail-bulk': 0 !== props.count,
      'fa-bell-slash': 0 === props.count
    })}
    label={trans('notifications')}
    subscript={0 !== props.count ? {
      type: 'label',
      status: 'primary',
      value: 100 > props.count ? props.count : '99+'
    } : undefined}
    tooltip="bottom"
    menu={
      <NotificationsMenu

      />
    }
  />

HeaderNotifications.propTypes = {
  count: T.number
}

HeaderNotifications.defaultProps = {
  count: 200
}

export {
  HeaderNotifications
}
