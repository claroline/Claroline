import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {PageFull} from '#/main/app/page/components/full'

import {MessagesNav} from '#/plugin/message/tools/messaging/components/messages-nav'
import {Messages} from '#/plugin/message/tools/messaging/components/messages'

const Messaging = () =>
  <PageFull
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
  </PageFull>


export {
  Messaging
}
