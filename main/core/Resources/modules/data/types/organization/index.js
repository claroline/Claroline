import {trans} from '#/main/app/intl/translation'

import {OrganizationDisplay} from '#/main/core/data/types/organization/components/display'
import {OrganizationInput} from '#/main/core/data/types/organization/components/input'

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
    input: OrganizationInput
  }
}

export {
  dataType
}
