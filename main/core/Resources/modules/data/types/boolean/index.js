import {t, tval} from '#/main/core/translation'

import {parseBool, translateBool} from '#/main/core/data/types/boolean/utils'
import {CheckGroup} from '#/main/core/layout/form/components/group/check-group.jsx'
import {BooleanSearch} from '#/main/core/data/types/boolean/components/search.jsx'
import {BooleanCell} from '#/main/core/data/types/boolean/components/table.jsx'

const BOOLEAN_TYPE = 'boolean'

const booleanDefinition = {
  meta: {
    type: BOOLEAN_TYPE,
    creatable: true,
    icon: 'fa fa-fw fa-check-square-o',
    label: t('boolean'),
    description: t('boolean_desc')
  },
  parse: (display) => parseBool(display),
  render: (raw) => translateBool(raw),

  validate: (value) => {
    try {
      parseBool(value)
    } catch (e) {
      return tval('This value should be a valid boolean.')
    }
  },
  components: {
    table: BooleanCell,
    search: BooleanSearch,
    form: CheckGroup
  }
}

export {
  BOOLEAN_TYPE,
  booleanDefinition
}
