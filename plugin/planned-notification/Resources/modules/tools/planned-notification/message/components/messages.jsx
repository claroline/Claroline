import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {DataListContainer} from '#/main/core/data/list/containers/data-list.jsx'
import {HtmlText} from '#/main/core/layout/components/html-text.jsx'

import {select} from '#/plugin/planned-notification/tools/planned-notification/selectors'

const MessagesList = props =>
  <DataListContainer
    name="messages.list"
    open={{
      action: (row) => `#/messages/form/${row.id}`
    }}
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
        type: 'string',
        displayed: true,
        renderer: (row) => {
          let contentRow =
            <HtmlText>
              {row.content}
            </HtmlText>

          return contentRow
        }
      }
    ]}
  />

MessagesList.propTypes = {
  canEdit: T.bool.isRequired,
  workspace: T.shape({
    uuid: T.string.isRequired
  }).isRequired
}

const Messages = connect(
  state => ({
    canEdit: select.canEdit(state),
    workspace: select.workspace(state)
  })
)(MessagesList)

export {
  Messages
}