import {trans} from '#/main/core/translation'

import {ChoiceGroup} from '#/main/core/layout/form/components/group/choice-group'
import {EnumSearch} from '#/main/core/data/types/enum/components/search'

const ENUM_TYPE = 'enum'

const enumDefinition = {
  meta: {
    type: ENUM_TYPE,
    creatable: true,
    icon: 'fa fa-fw fa fa-list',
    label: trans('enum'),
    description: trans('enum_desc')
  },
  parse: (display, options) => Object.keys(options.choices).find(enumValue => display === options.choices[enumValue]),
  render: (raw, options) => options.choices[raw],
  validate: (value, options) => !!options.choices[value],
  components: {
    search: EnumSearch,
    form: ChoiceGroup
  }
}

export {
  ENUM_TYPE,
  enumDefinition
}
