import {string} from '#/main/core/validation'

import {PasswordGroup} from '#/main/core/layout/form/components/group/password-group'

// todo handle password complexity options

const dataType = {
  name: 'password',
  validate: (value) => string(value),
  components: {
    form: PasswordGroup
  }
}

export {
  dataType
}
