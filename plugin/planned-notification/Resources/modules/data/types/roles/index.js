import isEmpty from 'lodash/isEmpty'
import {chain, array, string, notBlank} from '#/main/core/validation'

import {RolesInput} from '#/plugin/planned-notification/data/types/roles/components/input'

// todo : should be in core

const dataType = {
  name: 'workspace_roles',

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
    input: RolesInput
  }
}

export {
  dataType
}