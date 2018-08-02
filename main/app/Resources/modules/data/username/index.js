import {string} from '#/main/core/validation'

import {UsernameGroup} from '#/main/core/layout/form/components/group/username-group'

// todo : handle username regex option
// todo : handle uniqueness check

const dataType = {
  name: 'username',
  validate: (value) => string(value),
  components: {
    form: UsernameGroup
  }
}

export {
  dataType
}
