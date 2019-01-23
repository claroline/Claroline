import {trans} from '#/main/app/intl/translation'

import {OrganizationsDisplay} from '#/main/core/data/types/organizations/components/display'
import {OrganizationsGroup} from '#/main/core/data/types/organizations/components/group'

const dataType = {
  name: 'organizations',
  meta: {
    creatable: false,
    icon: 'fa fa-fw fa fa-building',
    label: trans('organizations'),
    description: trans('organizations_desc')
  },
  components: {
    details: OrganizationsDisplay,
    form: OrganizationsGroup
  }
}

export {
  dataType
}
