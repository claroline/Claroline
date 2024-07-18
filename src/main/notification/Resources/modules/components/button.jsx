import React, {useEffect} from 'react'
import {PropTypes as T} from 'prop-types'
import {useSelector} from 'react-redux'
import classes from 'classnames'

import {transChoice} from '#/main/app/intl'
import {useReducer} from '#/main/app/store/reducer'
import {Button} from '#/main/app/action'
import {MODAL_BUTTON} from '#/main/app/buttons'

import {reducer, selectors} from '#/main/notification/store'
import {MODAL_MY_NOTIFICATIONS} from '#/main/notification/modals/my-notifications'


const NotificationButton = (props) => {
  useReducer(selectors.STORE_NAME, reducer)

  const countNotifications = useSelector(selectors.count)

  useEffect(() => {

  })

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
