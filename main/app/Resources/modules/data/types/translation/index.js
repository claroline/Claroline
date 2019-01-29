import {trans} from '#/main/app/intl/translation'
import {chain, string} from '#/main/core/validation'

import {StringInput} from '#/main/app/data/types/string/components/input'

const dataType = {
  name: 'translation',
  meta: {
    icon: 'fa fa-fw fa fa-language',
    label: trans('translation', {}, 'data'),
    description: trans('translation_desc', {}, 'data')
  },

  render: (raw, options) => trans(raw, options.placeholders || {}, options.domain),
  validate: (value, options) => chain(value, options, [string]),
  components: {
    input: StringInput
  }
}

export {
  dataType
}
