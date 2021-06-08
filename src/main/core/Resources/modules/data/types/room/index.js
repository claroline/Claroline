import {trans} from '#/main/app/intl/translation'

import {RoomDisplay} from '#/main/core/data/types/room/components/display'
import {RoomFilter} from '#/main/core/data/types/room/components/filter'
import {RoomInput} from '#/main/core/data/types/room/components/input'

const dataType = {
  name: 'room',
  meta: {
    creatable: false,
    icon: 'fa fa-fw fa-door-open',
    label: trans('room', {}, 'data'),
    description: trans('room_desc', {}, 'data')
  },
  render: (raw) => raw ? raw.name : null,
  components: {
    details: RoomDisplay,
    input: RoomInput,
    search: RoomFilter
  }
}

export {
  dataType
}
