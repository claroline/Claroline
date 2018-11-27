import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {Modal} from '#/main/app/overlay/modal/components/modal'
import {ListData} from '#/main/app/content/list/containers/data'

import {selectors} from '#/plugin/planned-notification/modals/notifications/store'
import {NotificationList} from '#/plugin/planned-notification/tools/planned-notification/notification/components/notification-list'
import {Notification as NotificationType} from '#/plugin/planned-notification/tools/planned-notification/prop-types'

const NotificationsPickerModal = props => {
  const selectAction = props.selectAction(props.selected)

  return (
    <Modal
      {...omit(props, 'confirmText', 'selected', 'selectAction', 'resetSelect')}
      className="groups-picker-modal"
      icon="fa fa-fw fa-bell"
      bsSize="lg"
      onExiting={() => props.resetSelect()}
    >
      <ListData
        name={selectors.STORE_NAME}
        fetch={{
          url: ['apiv2_plannednotification_workspace_list', {workspace: props.workspaceId}],
          autoload: true
        }}
        definition={NotificationList.definition}
        card={NotificationList.card}
        display={props.display}
      />

      <Button
        label={props.confirmText}
        {...selectAction}
        className="modal-btn btn"
        primary={true}
        disabled={0 === props.selected.length}
        onClick={props.fadeModal}
      />
    </Modal>
  )
}

NotificationsPickerModal.propTypes = {
  workspaceId: T.string.isRequired,
  title: T.string,
  confirmText: T.string,
  selectAction: T.func.isRequired,
  fadeModal: T.func.isRequired,
  selected: T.arrayOf(T.shape(NotificationType.propTypes)).isRequired,
  resetSelect: T.func.isRequired
}

NotificationsPickerModal.defaultProps = {
  title: trans('notifications_picker', {}, 'planned_notification'),
  confirmText: trans('select', {}, 'actions')
}

export {
  NotificationsPickerModal
}
