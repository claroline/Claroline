import {t} from '#/main/core/translation'

import {SelectGroup} from '#/main/core/layout/form/components/group/select-group.jsx'
import {EnumSearch} from '#/main/core/data/types/enum/components/search.jsx'

const ENUM_TYPE = 'enum'

const enumDefinition = {
  meta: {
    type: ENUM_TYPE,
    creatable: true,
    icon: 'fa fa-fw fa fa-list',
    label: t('enum'),
    description: t('enum_desc')
  },
  parse: (display, options) => Object.keys(options.choices).find(enumValue => display === options.choices[enumValue]),
  render: (raw, options) => options.choices[raw],
  validate: (value, options) => !!options.choices[value],
  components: {
    search: EnumSearch,
    form: SelectGroup
  }
}

export {
  ENUM_TYPE,
  enumDefinition
}
