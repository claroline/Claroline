import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {Routes} from '#/main/app/router'

import {ToolPage} from '#/main/core/tool/containers/page'
import {Meta} from '#/main/core/administration/parameters/main/containers/meta'
import {Messages} from '#/main/core/administration/parameters/message/components/messages'
import {Message} from '#/main/core/administration/parameters/message/containers/message'
import {Technical} from '#/main/core/administration/parameters/technical/containers/technical'
import {PrivacyMain} from '#/main/core/administration/parameters/privacy/containers/main'

import {AppearanceTool} from '#/main/theme/administration/appearance/containers/tool'

const ParametersTool = (props) => {
  const parametersActions = []

  switch (props.location.pathname) {
    case props.path+'/messages':
      parametersActions.push({
        name: 'add',
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-plus',
        label: trans('add_connection_message'),
        target: props.path+'/messages/form',
        primary: true
      })
      break
  }

  return (
    <ToolPage
      className="main-settings-container"
      primaryAction="add"
      actions={parametersActions}
      subtitle={
        <Routes
          path={props.path}
          routes={[
            {path: '/', exact: true, render: () => trans('general')},
            {path: '/privacy',       render: () => trans('privacy')},
            {path: '/messages',      render: () => trans('connection_messages')},
            {path: '/technical',     render: () => trans('technical')},
            {path: '/appearance',    render: () => trans('appearance')}
          ]}
        />
      }
    >
      <Routes
        path={props.path}
        routes={[
          {
            path: '/',
            exact: true,
            component: Meta
          }, {
            path: '/privacy',
            component: PrivacyMain
          }, {
            path: '/messages',
            exact: true,
            render() {
              const MessagesList = (
                <Messages
                  path={props.path}
                />
              )

              return MessagesList
            }
          }, {
            path: '/messages/form/:id?', // TODO : should be declared in messages submodule
            component: Message,
            onEnter: (params) => props.openConnectionMessageForm(params.id),
            onLeave: () => props.resetConnectionMessageFrom()
          }, {
            path: '/technical',
            component: Technical
          }, {
            path: '/appearance',
            component: AppearanceTool
          }
        ]}
      />
    </ToolPage>
  )
}

ParametersTool.propTypes = {
  path: T.string,
  location: T.shape({
    pathname: T.string
  }),
  openConnectionMessageForm: T.func.isRequired,
  resetConnectionMessageFrom: T.func.isRequired
}

export {
  ParametersTool
}
