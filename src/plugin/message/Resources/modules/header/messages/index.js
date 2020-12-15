import {trans} from '#/main/app/intl/translation'

import {MessagesMenu} from '#/plugin/message/header/messages/containers/menu'

// expose main component to be used by the header
export default ({
  name: 'messages',
  label: trans('messages', {}, 'message'),
  component: MessagesMenu
})
