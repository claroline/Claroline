import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {Routes} from '#/main/app/router'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool/containers/page'

import {Message} from '#/main/core/administration/connection-messages/containers/message'
import {Messages} from '#/main/core/administration/connection-messages/components/messages'

const ConnectionMessagesTool = (props) => {
  const addActions = [
    {
      name: 'add',
      type: LINK_BUTTON,
      icon: 'fa fa-fw fa-plus',
      label: trans('add_connection_message', {}, 'actions'),
      target: `${props.path}/form`,
      displayed: true,
      primary : true
    }
  ]

  return (
    <ToolPage
      /*primaryAction="add"*/
      primaryAction={addActions}
      subtitle={trans('connection_messages', {}, 'tools')}
    >
      <Routes
        path={props.path}
        routes={[
          {
            path: '/form/:id?',
            component: Message,
            onEnter: (params) => props.openConnectionMessageForm(params.id),
            onLeave: () => props.resetConnectionMessageForm()
          },
          {
            path: '/',
            exact: true,
            render: () => <Messages path={props.path} />
          }
        ]}
      />
    </ToolPage>
  )
}

ConnectionMessagesTool.propTypes = {
  path: T.string,
  openConnectionMessageForm: T.func,
  resetConnectionMessageForm: T.func
}

export {
  ConnectionMessagesTool
}
