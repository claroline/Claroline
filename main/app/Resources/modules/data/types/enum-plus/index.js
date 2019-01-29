import {trans} from '#/main/app/intl/translation'

import {EnumPlusSearch} from '#/main/app/data/types/enum-plus/components/search'
import {isChoiceValid, parseChoice, renderChoice} from '#/main/app/data/types/enum-plus/utils'

const dataType = {
  name: 'enum-plus',
  meta: {
    creatable: true,
    icon: 'fa fa-fw fa fa-list',
    label: trans('enum_plus', {}, 'data'),
    description: trans('enum_plus_desc', {}, 'data')
  },
  parse: (display, options) => parseChoice(options.choices, display, options.transDomain),
  render: (raw, options) => renderChoice(options.choices, raw, options.transDomain),
  validate: (value, options) => !isChoiceValid(options.choices, value, options.transDomain),
  components: {
    search: EnumPlusSearch
  }
}

export {
  dataType
}
