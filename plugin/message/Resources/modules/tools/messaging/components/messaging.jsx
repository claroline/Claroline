import React from 'react'

import {trans} from '#/main/core/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {Page} from '#/main/app/page/components/page'

import {MessagesNav} from '#/plugin/message/tools/messaging/components/messages-nav'
import {Messages} from '#/plugin/message/tools/messaging/components/messages'

const Messaging = () =>
  <Page
    title={trans('messaging', {}, 'tools')}
    toolbar="send | more"
    actions={[
      {
        name: 'send',
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-paper-plane',
        label: trans('send-message', {}, 'actions'),
        target: '/new',
        primary: true
      }, {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-cog',
        label: trans('configure', {}, 'actions'),
        target: '/parameters'
      }
    ]}
  >
    <div className="row">
      <div className="col-md-3">
        <MessagesNav/>
      </div>
      <div className="col-md-9">
        <Messages/>
      </div>
    </div>
  </Page>


export {
  Messaging
}
