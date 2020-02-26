import React from 'react'
import isEmpty from 'lodash/isEmpty'

import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {ModalButton} from '#/main/app/buttons/modal/containers/button'

import {trans} from '#/main/app/intl/translation'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'
import {EmptyPlaceholder} from '#/main/app/content/components/placeholder'

import {MODAL_PLANNED_NOTIFICATIONS_PICKER} from '#/plugin/planned-notification/modals/notifications'
import {Notification as NotificationType} from '#/plugin/planned-notification/tools/planned-notification/prop-types'
import {NotificationCard} from '#/plugin/planned-notification/tools/planned-notification/notification/data/components/notification-card'

const NotificationsInput = props => {
  if (!isEmpty(props.value)) {
    return(
      <div>
        {props.value.map(notification =>
          <NotificationCard
            key={`notification-card-${notification.id}`}
            data={notification}
            size="sm"
            orientation="col"
            actions={[
              {
                name: 'delete',
                type: CALLBACK_BUTTON,
                icon: 'fa fa-fw fa-trash-o',
                label: trans('delete', {}, 'actions'),
                dangerous: true,
                callback: () => {
                  const newValue = props.value
                  const index = newValue.findIndex(n => n.id === notification.id)

                  if (-1 < index) {
                    newValue.splice(index, 1)
                    props.onChange(newValue)
                  }
                }
              }
            ]}
          />
        )}
        <ModalButton
          className="btn btn-notifications-primary"
          style={{marginTop: 10}}
          primary={true}
          modal={[MODAL_PLANNED_NOTIFICATIONS_PICKER, {
            title: props.picker.title,
            confirmText: props.picker.confirmText,
            selectAction: (selected) => ({
              type: CALLBACK_BUTTON,
              callback: () => {
                const newValue = props.value
                selected.forEach(notification => {
                  const index = newValue.findIndex(n => n.id === notification.id)

                  if (-1 === index) {
                    newValue.push(notification)
                  }
                })
                props.onChange(newValue)
              }
            })
          }]}
        >
          <span className="fa fa-fw fa-bell icon-with-text-right" />
          {trans('add_notifications', {}, 'planned_notification')}
        </ModalButton>
      </div>
    )
  } else {
    return (
      <EmptyPlaceholder
        size="lg"
        icon="fa fa-bell"
        title={trans('no_notification', {}, 'planned_notification')}
      >
        <ModalButton
          className="btn btn-notifications-primary"
          primary={true}
          modal={[MODAL_PLANNED_NOTIFICATIONS_PICKER, {
            title: props.picker.title,
            confirmText: props.picker.confirmText,
            selectAction: (selected) => ({
              type: CALLBACK_BUTTON,
              callback: () => props.onChange(selected)
            })
          }]}
        >
          <span className="fa fa-fw fa-bell icon-with-text-right" />
          {trans('add_notifications', {}, 'planned_notification')}
        </ModalButton>
      </EmptyPlaceholder>
    )
  }
}

implementPropTypes(NotificationsInput, DataInputTypes, {
  value: T.arrayOf(T.shape(NotificationType.propTypes)),
  picker: T.shape({
    title: T.string,
    confirmText: T.string
  })
}, {
  value: null,
  picker: {
    title: trans('notifications_picker', {}, 'planned_notification'),
    confirmText: trans('select', {}, 'actions')
  }
})

export {
  NotificationsInput
}
