import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {makeId} from '#/main/core/scaffolding/id'
import {trans} from '#/main/app/intl/translation'
import {Routes} from '#/main/app/router'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool/containers/page'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {selectors} from '#/plugin/planned-notification/tools/planned-notification/store'
import {actions} from '#/plugin/planned-notification/tools/planned-notification/message/actions'
import {Messages} from '#/plugin/planned-notification/tools/planned-notification/message/components/messages'
import {Message} from '#/plugin/planned-notification/tools/planned-notification/message/components/message'

const MessageTabComponent = props =>
  <ToolPage
    subtitle={trans('messages')}
    primaryAction="add"
    actions={[
      {
        name: 'add',
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-plus',
        label: trans('create_new_message', {}, 'planned_notification'),
        target: props.path+'/messages/form',
        displayed: props.canEdit,
        primary: true
      }
    ]}
  >
    <Routes
      path={props.path}
      routes={[
        {
          path: '/messages',
          exact: true,
          component: Messages
        }, {
          path: '/messages/form/:id?',
          component: Message,
          onEnter: (params) => props.openForm(params.id, props.workspace),
          onLeave: () => props.openForm(null, props.workspace)
        }
      ]}
    />
  </ToolPage>

MessageTabComponent.propTypes = {
  path: T.string.isRequired,
  canEdit: T.bool.isRequired,
  workspace: T.object.isRequired,
  openForm: T.func.isRequired
}

const MessageTab = connect(
  state => ({
    path: toolSelectors.path(state),
    canEdit: selectors.canEdit(state),
    workspace: selectors.workspace(state)
  }),
  dispatch => ({
    openForm(id, workspace) {
      const defaultValue = {
        id: makeId(),
        workspace: workspace
      }

      dispatch(actions.open(selectors.STORE_NAME+'.messages.current', id, defaultValue))
    }
  })
)(MessageTabComponent)

export {
  MessageTab
}
