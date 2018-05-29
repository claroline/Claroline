import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {makeId} from '#/main/core/scaffolding/id'
import {trans} from '#/main/core/translation'
import {matchPath, Routes, withRouter} from '#/main/app/router'

import {PageActions} from '#/main/core/layout/page/components/page-actions.jsx'
import {PageAction} from '#/main/core/layout/page'
import {FormPageActionsContainer} from '#/main/core/data/form/containers/page-actions.jsx'

import {select} from '#/plugin/planned-notification/tools/planned-notification/selectors'
import {actions} from '#/plugin/planned-notification/tools/planned-notification/message/actions'
import {Messages} from '#/plugin/planned-notification/tools/planned-notification/message/components/messages.jsx'
import {Message} from '#/plugin/planned-notification/tools/planned-notification/message/components/message.jsx'

const MessageTabEditActionsComponent = props =>
  <PageActions>
    <FormPageActionsContainer
      formName="messages.current"
      target={(message, isNew) => isNew ?
        ['apiv2_plannednotificationmessage_create'] :
        ['apiv2_plannednotificationmessage_update', {id: message.id}]
      }
      opened={!!matchPath(props.location.pathname, {path: '/messages/form'})}
      open={{
        type: 'link',
        icon: 'fa fa-plus',
        label: trans('create_new_message', {}, 'planned_notification'),
        target: '/messages/form'
      }}
      cancel={{
        type: 'link',
        target: '/messages'
      }}
    />
  </PageActions>

MessageTabEditActionsComponent.propTypes = {
  location: T.object
}

const MessageTabEditActions = withRouter(MessageTabEditActionsComponent)

const MessageTabActionsComponent = props =>
  <PageActions>
    {!!matchPath(props.location.pathname, {path: '/messages/form'}) &&
      <PageAction
        id="message-form-save"
        title={trans('cancel')}
        icon="fa fa-times"
        primary={false}
        action="#/messages"
      />
    }
  </PageActions>

MessageTabActionsComponent.propTypes = {
  location: T.object
}

const MessageTabActions = withRouter(MessageTabActionsComponent)

const MessageTabComponent = props =>
  <Routes
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

MessageTabComponent.propTypes = {
  canEdit: T.bool.isRequired,
  workspace: T.object.isRequired,
  openForm: T.func.isRequired
}

const MessageTab = connect(
  state => ({
    canEdit: select.canEdit(state),
    workspace: select.workspace(state)
  }),
  dispatch => ({
    openForm(id, workspace) {
      const defaultValue = {
        id: makeId(),
        workspace: workspace
      }

      dispatch(actions.open('messages.current', id, defaultValue))
    }
  })
)(MessageTabComponent)

export {
  MessageTabEditActions,
  MessageTabActions,
  MessageTab
}