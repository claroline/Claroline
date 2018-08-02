import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {makeId} from '#/main/core/scaffolding/id'
import {trans} from '#/main/core/translation'
import {Routes} from '#/main/app/router'
import {PageActions, PageAction} from '#/main/core/layout/page/components/page-actions'
import {LINK_BUTTON} from '#/main/app/buttons'

import {select} from '#/plugin/planned-notification/tools/planned-notification/selectors'
import {actions} from '#/plugin/planned-notification/tools/planned-notification/message/actions'
import {Messages} from '#/plugin/planned-notification/tools/planned-notification/message/components/messages'
import {Message} from '#/plugin/planned-notification/tools/planned-notification/message/components/message'

const MessageTabActions = () =>
  <PageActions>
    <PageAction
      type={LINK_BUTTON}
      icon="fa fa-plus"
      label={trans('create_new_message', {}, 'planned_notification')}
      target="/messages/form"
      primary={true}
    />
  </PageActions>

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
  workspace: T.object.isRequired,
  openForm: T.func.isRequired
}

const MessageTab = connect(
  state => ({
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
  MessageTabActions,
  MessageTab
}
