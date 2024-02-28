import {trans} from '#/main/app/intl/translation'

import {EventDisplay} from '#/plugin/cursus/data/types/event/components/display'
import {EventInput} from '#/plugin/cursus/data/types/event/components/input'
import {EventCell} from '#/plugin/cursus/data/types/event/components/cell'
import {EventFilter} from '#/plugin/cursus/data/types/event/components/filter'

const dataType = {
  name: 'training_event',
  meta: {
    creatable: false,
    icon: 'fa fa-fw fa fa-calendar-day',
    label: trans('training_event', {}, 'data'),
    description: trans('training_event_desc', {}, 'data')
  },
  render: (raw) => raw ? raw.name : null,
  components: {
    details: EventDisplay,
    input: EventInput,
    table: EventCell,
    search: EventFilter
  }
}

export {
  dataType
}
