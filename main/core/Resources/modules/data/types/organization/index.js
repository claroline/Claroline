import {trans} from '#/main/app/intl/translation'

import {OrganizationInput} from '#/main/core/data/types/organization/components/input'

const dataType = {
  name: 'organization',
  meta: {
    icon: 'fa fa-fw fa fa-building',
    label: trans('organization', {}, 'data'),
    description: trans('organization_desc', {}, 'data')
  },
  render: (raw) => raw ? raw.name : null,
  components: {
    input: OrganizationInput
  }
}

export {
  dataType
}
