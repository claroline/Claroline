import {trans} from '#/main/app/intl/translation'

import {TeamDisplay} from '#/main/community/data/types/team/components/display'
import {TeamInput} from '#/main/community/data/types/team/components/input'
import {TeamFilter} from '#/main/community/data/types/team/components/filter'

const dataType = {
  name: 'team',
  meta: {
    creatable: false,
    icon: 'fa fa-fw fa fa-users',
    label: trans('team', {}, 'data'),
    description: trans('team_desc', {}, 'data')
  },
  render: (raw) => raw && raw.map(t => t.name).join(', '),
  components: {
    details: TeamDisplay,
    input: TeamInput,
    search: TeamFilter
  }
}

export {
  dataType
}
