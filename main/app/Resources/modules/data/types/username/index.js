import {tval} from '#/main/app/intl/translation'
import {chain, string, notExist} from '#/main/app/data/types/validators'

import {UsernameInput} from '#/main/app/data/types/username/components/input'

// todo : handle username regex option
// todo : handle uniqueness check

const dataType = {
  name: 'username',
  validate: (value, options) => {
    if (options.unique && !options.unique.error) {
      options.unique.error = tval('This username already exists.')
    }

    return chain(value, options, [string, notExist])
  },
  components: {
    input: UsernameInput
  }
}

export {
  dataType
}
