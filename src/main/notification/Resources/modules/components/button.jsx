import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {trans, transChoice} from '#/main/app/intl'
import {Button} from '#/main/app/action'
import {MODAL_BUTTON} from '#/main/app/buttons'

import {MODAL_MY_NOTIFICATIONS} from '#/main/notification/modals/my-notifications'

const NotificationButton = (props) => {
  const countNotifications = 1

  return (
    <Button
      {...props}
      type={MODAL_BUTTON}
      icon={classes('fa-fw fa-bell', {
        'fa': !!countNotifications,
        'far': !countNotifications
      })}
      label={transChoice('notifications_count', countNotifications, {count: countNotifications}, 'notification')}
      modal={[MODAL_MY_NOTIFICATIONS]}
    >
      {0 !== countNotifications &&
        <span className="notification-status position-absolute top-100 start-100 translate-middle m-n1 text-bg-danger rounded-circle p-1">
          {countNotifications}
        </span>
      }
    </Button>
  )
}

NotificationButton.propTypes = {
  className: T.string
}

export {
  NotificationButton
}
