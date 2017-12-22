import {string} from '#/main/core/validation'

import {UsernameGroup} from '#/main/core/layout/form/components/group/username-group.jsx'

const USERNAME_TYPE = 'username'

// todo : handle username regex option
// todo : handle uniqueness check

const usernameDefinition = {
  meta: {
    type: USERNAME_TYPE
  },

  // nothing special to do
  parse: (display) => display,
  // nothing special to do
  render: (raw) => raw,
  validate: (value) => string(value),
  components: {
    form: UsernameGroup
  }
}

export {
  USERNAME_TYPE,
  usernameDefinition
}
