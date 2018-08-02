import {trans, tval} from '#/main/core/translation'

import {parseBool, translateBool} from '#/main/app/data/boolean/utils'

import {CheckGroup} from '#/main/core/layout/form/components/group/check-group'
import {BooleanCell} from '#/main/app/data/boolean/components/cell'
import {BooleanDisplay} from '#/main/app/data/boolean/components/display'
import {BooleanFilter} from '#/main/app/data/boolean/components/filter'

const dataType = {
  name: 'boolean',
  meta: {
    creatable: true,
    icon: 'fa fa-fw fa-check-square-o',
    label: trans('boolean'),
    description: trans('boolean_desc'),
    noLabel: true // todo : implement
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
    details: BooleanDisplay,
    table: BooleanCell,
    search: BooleanFilter,
    form: CheckGroup
  }
}

export {
  dataType
}
