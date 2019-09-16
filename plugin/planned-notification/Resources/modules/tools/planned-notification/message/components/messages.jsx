import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'
import {MODAL_USERS} from '#/main/core/modals/users'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {selectors} from '#/plugin/planned-notification/tools/planned-notification/store'
import {actions} from '#/plugin/planned-notification/tools/planned-notification/message/actions'

const MessagesList = props =>
  <ListData
    name={selectors.STORE_NAME+'.messages.list'}
    primaryAction={(row) => ({
      type: LINK_BUTTON,
      label: trans('open'),
      target: `${props.path}/messages/form/${row.id}`
    })}
    fetch={{
      url: ['apiv2_plannednotificationmessage_workspace_list', {workspace: props.workspace.uuid}],
      autoload: true
    }}
    delete={{
      url: ['apiv2_plannednotificationmessage_delete_bulk'],
      displayed: () => props.canEdit
    }}
    definition={[
      {
        name: 'title',
        label: trans('title'),
        type: 'string',
        displayed: true
      }, {
        name: 'content',
        label: trans('content'),
        type: 'html',
        displayed: true
      }
    ]}
    actions={(rows) => [
      {
        name: 'send',
        type: MODAL_BUTTON,
        icon: 'fa fa-fw fa-envelope-o',
        label: trans('send'),
        modal: [MODAL_USERS, {
          selectAction: (selected) => ({
            type: CALLBACK_BUTTON,
            callback: () => props.sendMessages(rows, selected)
          })
        }]
      }
    ]}
  />

MessagesList.propTypes = {
  path: T.string.isRequired,
  canEdit: T.bool.isRequired,
  workspace: T.shape({
    uuid: T.string.isRequired
  }).isRequired,
  sendMessages: T.func.isRequired
}

const Messages = connect(
  state => ({
    path: toolSelectors.path(state),
    canEdit: selectors.canEdit(state),
    workspace: selectors.workspace(state)
  }),
  dispatch => ({
    sendMessages(messages, users) {
      dispatch(actions.sendMessages(messages, users))
    }
  })
)(MessagesList)

export {
  Messages
}
