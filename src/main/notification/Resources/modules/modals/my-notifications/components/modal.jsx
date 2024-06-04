import React from 'react'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {trans} from '#/main/app/intl'

const MyNotificationsModal = (props) =>
  <Modal
    {...props}
    title={trans('my_notifications', {}, 'notification')}
  >
    Notifications et Rappels
  </Modal>

export {
  MyNotificationsModal
}
