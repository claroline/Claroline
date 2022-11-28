import {createElement} from 'react'

import {trans, tval} from '#/main/app/intl/translation'
import {chain, string, email, notExist} from '#/main/app/data/types/validators'

import {EmailInput} from '#/main/app/data/types/email/components/input'

const dataType = {
  name: 'email',
  meta: {
    icon: 'fa fa-fw fa-at',
    label: trans('email', {}, 'data'),
    description: trans('email_desc', {}, 'data'),
    creatable: true
  },
  render: (raw) => createElement('a', {href: `mailto:${raw}`}, raw),
  validate: (value, options) => {
    if (options.unique && !options.unique.error) {
      options.unique.error = tval('This email already exists.')
    }

    return chain(value, options, [string, email, notExist])
  },
  components: {
    input: EmailInput
  }
}

export {
  dataType
}
