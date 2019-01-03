import {trans, tval} from '#/main/app/intl/translation'

import {boolean} from '#/main/app/data/boolean/validators'
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
    label: trans('boolean', {}, 'data'),
    description: trans('boolean_desc', {}, 'data'),
    noLabel: true
  },
  parse: (display) => parseBool(display),
  render: (raw) => translateBool(raw),

  validate: (value) => boolean(value),
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
