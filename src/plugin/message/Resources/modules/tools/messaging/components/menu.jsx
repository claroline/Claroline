import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ToolMenu} from '#/main/core/tool/containers/menu'

const MessagingMenu = (props) =>
  <ToolMenu
    actions={[
      {
        name: 'inbox',
        type: LINK_BUTTON,
        //icon: 'fa fa-fw fa-inbox',
        label: trans('messages_received', {}, 'message'),
        target: props.path+'/received'
      }, {
        name: 'sent',
        type: LINK_BUTTON,
        //icon: 'fa fa-fw fa-paper-plane',
        label: trans('messages_sent', {}, 'message'),
        target: props.path+'/sent'
      }, {
        name: 'deleted',
        type: LINK_BUTTON,
        //icon: 'fa fa-fw fa-trash',
        label: trans('messages_removed', {}, 'message'),
        target: props.path+'/deleted'
      }, {
        name: 'contact',
        type: LINK_BUTTON,
        //icon: 'fa fa-fw fa-address-book',
        label: trans('contacts', {}, 'message'),
        target: props.path+'/contacts'
      }
    ]}
  />

MessagingMenu.propTypes = {
  path: T.string.isRequired
}

export {
  MessagingMenu
}
