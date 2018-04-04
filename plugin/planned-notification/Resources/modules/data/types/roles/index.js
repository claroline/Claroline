import isEmpty from 'lodash/isEmpty'
import {chain, array, string, notBlank} from '#/main/core/validation'

import {RolesFormGroup} from '#/plugin/planned-notification/data/types/roles/components/roles-form-group.jsx'

const WORKSPACE_ROLES_TYPE = 'workspace_roles'

const rolesDefinition = {
  meta: {
    type: WORKSPACE_ROLES_TYPE
  },

  validate: (value, options) => chain(value, options, [array, (value) => {
    if (value) {
      const errors = {}

      value.map((role, index) => {
        const error = chain(role.id, {isHtml: false}, [string, notBlank])

        if (error) {
          errors[index] = error
        }
      })

      if (!isEmpty(errors)) {
        return errors
      }
    }
  }]),

  components: {
    form: RolesFormGroup
  }
}

export {
  WORKSPACE_ROLES_TYPE,
  rolesDefinition
}