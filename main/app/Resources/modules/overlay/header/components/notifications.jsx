import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {trans} from '#/main/core/translation'
import {Button} from '#/main/app/action/components/button'
import {URL_BUTTON, MENU_BUTTON} from '#/main/app/buttons'

const NotificationsMenu = props =>
  <ul className="app-notifications dropdown-menu dropdown-menu-right">
    <li role="presentation">
      <Button
        type={URL_BUTTON}
        icon="fa fa-fw fa-bell"
        subscript={0 !== props.count.notifications ? {
          type: 'label',
          status: 'primary',
          value: 100 > props.count.notifications ? props.count.notifications : '99+'
        } : undefined}
        label={trans('show-notifications', {}, 'actions')}
        target={['icap_notification_view']}
      />
      <Button
        type={URL_BUTTON}
        icon="fa fa-fw fa-envelope"
        subscript={0 !== props.count.messages ? {
          type: 'label',
          status: 'primary',
          value: 100 > props.count.messages ? props.count.messages : '99+'
        } : undefined}
        label={trans('show-messages', {}, 'actions')}
        target={['claro_message_index']}
      />
    </li>
  </ul>

NotificationsMenu.propTypes = {
  count: T.shape({
    notifications: T.number,
    messages: T.number
  })
}

const HeaderNotifications = props => {
  let totalCount = props.count.notifications + props.count.messages
  return (
    <Button
      id="app-notifications-menu"
      type={MENU_BUTTON}
      className="app-header-item app-header-btn"
      icon={classes('fa fa-fw', {
        'fa-mail-bulk': 0 !== totalCount,
        'fa-bell-slash': 0 === totalCount
      })}
      label={trans('notifications')}
      subscript={0 !== totalCount ? {
        type: 'label',
        status: 'primary',
        value: 100 > totalCount ? totalCount : '99+'
      } : undefined}
      tooltip="bottom"
      menu={
        <NotificationsMenu
          count={props.count}
        />
      }
    />
  )
}


HeaderNotifications.propTypes = {
  count: T.shape({
    notifications: T.number,
    messages: T.number
  })
}


export {
  HeaderNotifications
}
