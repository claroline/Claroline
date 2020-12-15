import {trans} from '#/main/app/intl/translation'

import {ConnectionMessageDisplay} from '#/main/core/data/types/connection-message/components/display'
import {ConnectionMessageInput} from '#/main/core/data/types/connection-message/components/input'
import {ConnectionMessageCell} from '#/main/core/data/types/connection-message/components/cell'

const dataType = {
  name: 'connection-message',
  meta: {
    creatable: false,
    icon: 'fa fa-fw fa fa-comment-dots',
    label: trans('connection_message'),
    description: trans('connection_message_desc')
  },
  components: {
    details: ConnectionMessageDisplay,
    input: ConnectionMessageInput,
    table: ConnectionMessageCell
  }
}

export {
  dataType
}
