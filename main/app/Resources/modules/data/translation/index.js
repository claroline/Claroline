import {trans} from '#/main/core/translation'
import {chain, string} from '#/main/core/validation'

import {TextGroup} from '#/main/core/layout/form/components/group/text-group'

const dataType = {
  name: 'translation',
  meta: {
    icon: 'fa fa-fw fa fa-language',
    label: trans('translation'),
    description: trans('translation_desc')
  },

  render: (raw, options) => trans(raw, options.placeholders || {}, options.domain),
  validate: (value, options) => chain(value, options, [string]),
  components: {
    form: TextGroup
  }
}

export {
  dataType
}
