import {trans} from '#/main/core/translation'

//import {SelectGroup} from '#/main/core/layout/form/components/group/select-group.jsx'
import {EnumPlusSearch} from '#/main/app/data/enum-plus/components/search.jsx'
import {isChoiceValid, parseChoice, renderChoice} from '#/main/app/data/enum-plus/utils'

const dataType = {
  name: 'enum-plus',
  meta: {
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
  dataType
}
