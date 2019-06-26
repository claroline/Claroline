import {trans} from '#/main/app/intl/translation'

import {RolesDisplay} from '#/main/core/data/types/roles/components/display'
import {RolesInput} from '#/main/core/data/types/roles/components/input'

const dataType = {
  name: 'roles',
  meta: {
    icon: 'fa fa-fw fa fa-id-badge',
    label: trans('roles', {}, 'data'),
    description: trans('roles_desc', {}, 'data')
  },
  components: {
    details: RolesDisplay,
    input: RolesInput
  }
}

export {
  dataType
}
