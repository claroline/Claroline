import {trans} from '#/main/app/intl/translation'

import {OrganizationDisplay} from '#/main/core/data/types/organization/components/display'
import {OrganizationGroup} from '#/main/core/data/types/organization/components/group'

const dataType = {
  name: 'organization',
  meta: {
    creatable: false,
    icon: 'fa fa-fw fa fa-organization-arrow',
    label: trans('organization'),
    description: trans('organization_desc')
  },
  components: {
    details: OrganizationDisplay,
    form: OrganizationGroup
  }
}

export {
  dataType
}
