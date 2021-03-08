import {trans} from '#/main/app/intl/translation'

import {SessionDisplay} from '#/plugin/cursus/data/types/session/components/display'
import {SessionInput} from '#/plugin/cursus/data/types/session/components/input'
import {SessionCell} from '#/plugin/cursus/data/types/session/components/cell'
import {SessionFilter} from '#/plugin/cursus/data/types/session/components/filter'

const dataType = {
  name: 'training_session',
  meta: {
    creatable: false,
    icon: 'fa fa-fw fa fa-calendar-week',
    label: trans('training_session', {}, 'data'),
    description: trans('training_session_desc', {}, 'data')
  },
  render: (raw) => raw ? raw.name : null,
  components: {
    details: SessionDisplay,
    input: SessionInput,
    table: SessionCell,
    search: SessionFilter
  }
}

export {
  dataType
}
