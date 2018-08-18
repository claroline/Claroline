import React from 'react'

import {trans} from '#/main/core/translation'
import {Vertical} from '#/main/app/content/tabs/components/vertical'


const MessagesNav = () =>
  <Vertical
    tabs={[
      {
        icon: 'fa fa-fw fa-envelope',
        title: trans('messages_received'),
        path: '/received'
      }, {
        icon: 'fa fa-fw fa-share',
        title: trans('messages_sent'),
        path: '/sent'
      },  {
        icon: 'fa fa-fw fa-trash',
        title: trans('messages_removed'),
        path: '/deleted'
      }, {
        icon: 'fa fa-fw fa-plus',
        title: trans('new_message'),
        path: '/new'
      }, {
        icon: 'fa fa-fw fa-cogs',
        title: trans('preferences'),
        path: '/parameters'
      }
    ]}
  />


export {
  MessagesNav
}
