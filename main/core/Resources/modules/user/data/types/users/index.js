import {OrganizationGroup} from '#/main/core/user/data/types/organization/components/form-group.jsx'

const USERS_TYPE = 'users'

import {t} from '#/main/core/translation'

const usersDefinition = {
  meta: {
    type: USERS_TYPE,
    creatable: false,
    label: t('user'),
    description: t('user_desc')
  },
  render: (raw) => raw ? raw.name : null,
  components: {
    form: OrganizationGroup
  }
}

export {
  USERS_TYPE,
  usersDefinition
}
