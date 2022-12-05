import {trans} from '#/main/app/intl/translation'

import {boolean} from '#/main/app/data/types/boolean/validators'
import {parseBool, translateBool} from '#/main/app/data/types/boolean/utils'

import {BooleanInput} from '#/main/app/data/types/boolean/components/input'
import {BooleanGroup} from '#/main/app/data/types/boolean/components/group'
import {BooleanCell} from '#/main/app/data/types/boolean/components/cell'
import {BooleanDisplay} from '#/main/app/data/types/boolean/components/display'
import {BooleanFilter} from '#/main/app/data/types/boolean/components/filter'

const dataType = {
  name: 'boolean',
  meta: {
    icon: 'fa fa-fw fa-check-square',
    label: trans('boolean', {}, 'data'),
    description: trans('boolean_desc', {}, 'data'),
    creatable: true
  },
  parse: parseBool,
  render: translateBool,

  validate: boolean,
  components: {
    // old api
    details: BooleanDisplay,
    table: BooleanCell,
    search: BooleanFilter,

    // new api
    group: BooleanGroup,
    input: BooleanInput,
    display: BooleanDisplay,
    filter: BooleanFilter,
    cell: BooleanCell
  }
}

export {
  dataType
}
