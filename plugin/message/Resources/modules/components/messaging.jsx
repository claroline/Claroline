import React from 'react'

import {PageContainer} from '#/main/core/layout/page'

import {MessagesNav} from '#/plugin/message/components/messages-nav'
import {Messages} from '#/plugin/message/components/messages'

const Messaging = () =>
  <PageContainer>
    <div className="row">
      <div className="col-md-3">
        <MessagesNav/>
      </div>
      <div className="col-md-9">
        <Messages/>
      </div>
    </div>
  </PageContainer>


export {
  Messaging
}
