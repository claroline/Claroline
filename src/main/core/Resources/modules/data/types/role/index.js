import {trans} from '#/main/app/intl/translation'

import {RoleDisplay} from '#/main/core/data/types/role/components/display'
import {RoleInput} from '#/main/core/data/types/role/components/input'
import {RoleFilter} from '#/main/core/data/types/role/components/filter'

const dataType = {
  name: 'role',
  meta: {
    creatable: false,
    icon: 'fa fa-fw fa fa-id-card',
    label: trans('role'),
    description: trans('role_desc')
  },
  components: {
    details: RoleDisplay,
    input: RoleInput,
    search: RoleFilter
  }
}

export {
  dataType
}
