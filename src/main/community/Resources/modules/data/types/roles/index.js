import {trans} from '#/main/app/intl/translation'

import {RolesDisplay} from '#/main/community/data/types/roles/components/display'
import {RolesInput} from '#/main/community/data/types/roles/components/input'
import {RolesFilter} from '#/main/community/data/types/roles/components/filter'

const dataType = {
  name: 'roles',
  meta: {
    icon: 'fa fa-fw fa fa-id-badge',
    label: trans('roles', {}, 'data'),
    description: trans('roles_desc', {}, 'data')
  },
  render: (raw) => raw && raw.map(r => trans(r.translationKey)).join(', '),
  components: {
    details: RolesDisplay,
    input: RolesInput,
    search: RolesFilter
  }
}

export {
  dataType
}
