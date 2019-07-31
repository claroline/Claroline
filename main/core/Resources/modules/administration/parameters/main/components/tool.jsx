import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {Routes} from '#/main/app/router'

import {ToolPage} from '#/main/core/tool/containers/page'
import {Home} from '#/main/core/administration/parameters/main/components/home'
import {Archive} from '#/main/core/administration/parameters/main/components/archive'
import {Meta} from '#/main/core/administration/parameters/main/components/meta'
import {I18n} from '#/main/core/administration/parameters/main/components/i18n'
import {Plugins} from '#/main/core/administration/parameters/main/components/plugins'
import {Maintenance} from '#/main/core/administration/parameters/main/components/maintenance'
import {Messages} from '#/main/core/administration/parameters/main/components/messages'
import {Message} from '#/main/core/administration/parameters/main/components/message'

const ParametersTool = (props) =>
  <ToolPage
    className="main-settings-container"
    actions={props.path+'/messages' === props.location.pathname ? [
      {
        name: 'add',
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-plus',
        label: trans('add_connection_message'),
        target: props.path+'/messages/form',
        primary: true
      }
    ] : []}
    subtitle={
      <Routes
        path={props.path}
        routes={[
          {path: '/', exact: true, render: () => trans('information')},
          {path: '/home',          render: () => trans('home')},
          {path: '/i18n',          render: () => trans('language')},
          {path: '/plugins',       render: () => trans('plugins')},
          {path: '/maintenance',   render: () => trans('maintenance')},
          {path: '/archives',      render: () => trans('archive')},
          {path: '/messages',      render: () => trans('connection_messages')}
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
          path: '/home',
          component: Home
        }, {
          path: '/i18n',
          component: I18n
        }, {
          path: '/plugins',
          component: Plugins
        }, {
          path: '/maintenance',
          component: Maintenance
        }, {
          path: '/archives',
          component: Archive
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
        }
      ]}
    />
  </ToolPage>

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
