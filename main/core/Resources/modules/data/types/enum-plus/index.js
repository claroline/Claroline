import {trans} from '#/main/core/translation'

//import {SelectGroup} from '#/main/core/layout/form/components/group/select-group.jsx'
import {EnumPlusSearch} from '#/main/core/data/types/enum-plus/components/search.jsx'
import {isChoiceValid, parseChoice, renderChoice} from '#/main/core/data/types/enum-plus/utils'

const ENUM_PLUS_TYPE = 'enum-plus'

const enumPlusDefinition = {
  meta: {
    type: ENUM_PLUS_TYPE,
    creatable: true,
    icon: 'fa fa-fw fa fa-list',
    label: trans('enum_plus'),
    description: trans('enum_plus_desc')
  },
  parse: (display, options) => parseChoice(options.choices, display, options.transDomain),
  render: (raw, options) => renderChoice(options.choices, raw, options.transDomain),
  validate: (value, options) => !isChoiceValid(options.choices, value, options.transDomain),
  components: {
    search: EnumPlusSearch
  }
}

export {
  ENUM_PLUS_TYPE,
  enumPlusDefinition
}
