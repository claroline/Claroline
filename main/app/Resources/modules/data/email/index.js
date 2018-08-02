import {trans} from '#/main/core/translation'
import {chain, string, email} from '#/main/core/validation'

import {EmailLink} from '#/main/core/layout/button/components/email-link.jsx'
import {TextGroup} from '#/main/core/layout/form/components/group/text-group.jsx'

const dataType = {
  name: 'email',
  meta: {
    creatable: true,
    icon: 'fa fa-fw fa-at',
    label: trans('email'),
    description: trans('email_desc')
  },
  validate: (value) => chain(value, {}, [string, email]),
  components: {
    form: TextGroup,
    details: EmailLink,
    table: EmailLink
  }
}

export {
  dataType
}
