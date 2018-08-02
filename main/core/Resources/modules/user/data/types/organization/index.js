import {OrganizationGroup} from '#/main/core/user/data/types/organization/components/form-group'

import {trans} from '#/main/core/translation'

const dataType = {
  name: 'organization',
  meta: {
    creatable: false,
    label: trans('organization'),
    description: trans('organization_desc')
  },
  render: (raw) => raw ? raw.name : null,
  components: {
    form: OrganizationGroup
  }
}

export {
  dataType
}
