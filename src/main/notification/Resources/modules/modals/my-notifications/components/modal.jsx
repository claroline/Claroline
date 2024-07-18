import React, {useEffect} from 'react'
import {useDispatch} from 'react-redux'

import {trans} from '#/main/app/intl'
import {Modal} from '#/main/app/overlays/modal/components/modal'

import {actions} from '#/main/notification/modals/my-notifications/store'

const MyNotificationsModal = (props) => {
  const dispatch = useDispatch()

  useEffect(() => {
    dispatch(actions.fetchNotifications())
  })

  return (
    <Modal
      {...props}
      title={trans('my_notifications', {}, 'notification')}
    >
      <div className="modal-body" role="presentation">
        Notifications et Rappels
      </div>
    </Modal>
  )
}

export {
  MyNotificationsModal
}
