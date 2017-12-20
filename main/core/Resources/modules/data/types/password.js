import {string} from '#/main/core/validation'

import {PasswordGroup} from '#/main/core/layout/form/components/group/password-group.jsx'

const PASSWORD_TYPE = 'password'

// todo handle password complexity options

const passwordDefinition = {
  meta: {
    type: PASSWORD_TYPE
  },
  // nothing special to do
  parse: (display) => display,
  // nothing special to do
  render: (raw) => raw,
  validate: (value) => string(value),
  components: {
    form: PasswordGroup
  }
}

export {
  PASSWORD_TYPE,
  passwordDefinition
}
