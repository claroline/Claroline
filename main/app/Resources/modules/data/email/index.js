import {trans} from '#/main/app/intl/translation'
import {chain, string, email} from '#/main/core/validation'

import {EmailDisplay} from '#/main/app/data/email/components/display'
import {EmailGroup} from '#/main/core/layout/form/components/group/email-group'

const dataType = {
  name: 'email',
  meta: {
    creatable: true,
    icon: 'fa fa-fw fa-at',
    label: trans('email', {}, 'data'),
    description: trans('email_desc', {}, 'data')
  },
  validate: (value) => chain(value, {}, [string, email]),
  components: {
    form: EmailGroup,
    details: EmailDisplay,
    table: EmailDisplay
  }
}

export {
  dataType
}
