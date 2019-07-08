import {string} from '#/main/app/data/types/validators'

import {UsernameInput} from '#/main/app/data/types/username/components/input'

// todo : handle username regex option
// todo : handle uniqueness check

const dataType = {
  name: 'username',
  validate: (value) => string(value),
  components: {
    input: UsernameInput
  }
}

export {
  dataType
}
