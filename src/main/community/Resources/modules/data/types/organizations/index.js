import {trans} from '#/main/app/intl/translation'

import {OrganizationsDisplay} from '#/main/community/data/types/organizations/components/display'
import {OrganizationsInput} from '#/main/community/data/types/organizations/components/input'
import {OrganizationsFilter} from '#/main/community/data/types/organizations/components/filter'

const dataType = {
  name: 'organizations',
  meta: {
    creatable: false,
    icon: 'fa fa-fw fa fa-building',
    label: trans('organizations', {}, 'data'),
    description: trans('organizations_desc', {}, 'data')
  },
  components: {
    details: OrganizationsDisplay,
    input: OrganizationsInput,
    search: OrganizationsFilter
  }
}

export {
  dataType
}
