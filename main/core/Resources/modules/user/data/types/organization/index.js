import {OrganizationGroup} from '#/main/core/user/data/types/organization/components/form-group.jsx'

const ORGANIZATION_TYPE = 'organization'

import {t} from '#/main/core/translation'

const organizationDefinition = {
  meta: {
    type: ORGANIZATION_TYPE,
    creatable: false,
    label: t('organization'),
    description: t('organization_desc')
  },
  render: (raw) => raw ? raw.name : null,
  components: {
    form: OrganizationGroup
  }
}

export {
  ORGANIZATION_TYPE,
  organizationDefinition
}
