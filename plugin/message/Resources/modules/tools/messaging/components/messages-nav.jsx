import React from 'react'

import {trans} from '#/main/core/translation'
import {Vertical} from '#/main/app/content/tabs/components/vertical'

const MessagesNav = () =>
  <Vertical
    tabs={[
      {
        icon: 'fa fa-fw fa-inbox',
        title: trans('messages_received', {}, 'message'),
        path: '/received'
      }, {
        icon: 'fa fa-fw fa-paper-plane',
        title: trans('messages_sent', {}, 'message'),
        path: '/sent'
      },  {
        icon: 'fa fa-fw fa-trash',
        title: trans('messages_removed', {}, 'message'),
        path: '/deleted'
      }
    ]}
  />


export {
  MessagesNav
}
