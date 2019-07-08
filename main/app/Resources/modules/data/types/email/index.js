import {trans} from '#/main/app/intl/translation'
import {chain, string, email} from '#/main/app/data/types/validators'

import {EmailDisplay} from '#/main/app/data/types/email/components/display'
import {EmailInput} from '#/main/app/data/types/email/components/input'

const dataType = {
  name: 'email',
  meta: {
    icon: 'fa fa-fw fa-at',
    label: trans('email', {}, 'data'),
    description: trans('email_desc', {}, 'data'),
    creatable: true
  },
  validate: (value) => chain(value, {}, [string, email]),
  components: {
    input: EmailInput,
    details: EmailDisplay,
    table: EmailDisplay
  }
}

export {
  dataType
}
