import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'
import {constants as listConstants} from '#/main/app/content/list/constants'
import {AccountPage} from '#/main/app/account/containers/page'

import {NotificationCard} from '#/plugin/notification/components/card'
import {selectors} from '#/plugin/notification/account/notifications/store/selectors'
import {ContentSizing} from '#/main/app/content/components/sizing'

const NotificationMain = (props) =>
  <AccountPage
    title={trans('notifications')}
  >
    <ContentSizing size="full">
      <ListData
        flush={true}
        name={selectors.LIST_NAME}
        fetch={{
          url: ['apiv2_user_notifications_list'],
          autoload: true
        }}
        display={{
          available: [
            listConstants.DISPLAY_TABLE_SM,
            listConstants.DISPLAY_TABLE,
            listConstants.DISPLAY_LIST_SM
          ],
          current: listConstants.DISPLAY_LIST_SM
        }}
        definition={[
          {
            name: 'notification.meta.creator',
            type: 'user',
            label: trans('user'),
            displayed: true,
            filterable: false
          }, {
            name: 'text',
            type: 'string',
            label: trans('message'),
            displayed: true,
            sortable: false,
            filterable: false
          }, {
            name: 'notification.meta.created',
            type: 'date',
            label: trans('date'),
            displayed: true,
            options: {
              time: true
            }
          }
        ]}
        card={NotificationCard}
        actions={(rows) => [
          {
            name: 'read',
            type: CALLBACK_BUTTON,
            icon: 'fa fa-fw fa-check',
            label: trans('mark-as-read', {}, 'actions'),
            displayed: -1 !== rows.findIndex(row => !row.read),
            callback: () => props.markAsRead(rows)
          }, {
            name: 'unread',
            type: CALLBACK_BUTTON,
            icon: 'fa fa-fw fa-recycle',
            label: trans('mark-as-unread', {}, 'actions'),
            displayed: -1 !== rows.findIndex(row => row.read),
            callback: () => props.markAsUnread(rows)
          }, {
            name: 'delete',
            type: CALLBACK_BUTTON,
            icon: 'fa fa-fw fa-trash',
            label: trans('delete', {}, 'actions'),
            callback: () => props.delete(rows),
            confirm: true,
            dangerous: true
          }
        ]}
      />
    </ContentSizing>
  </AccountPage>

NotificationMain.propTypes = {
  delete: T.func.isRequired,
  markAsRead: T.func.isRequired,
  markAsUnread: T.func.isRequired
}

export {
  NotificationMain
}
