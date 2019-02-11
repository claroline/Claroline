import {trans} from '#/main/app/intl/translation'
import {chain, string} from '#/main/core/validation'

import {color} from '#/main/app/data/types/color/validators'
import {ColorCell} from '#/main/app/data/types/color/components/cell'
import {ColorInput} from '#/main/app/data/types/color/components/input'

const dataType = {
  name: 'color',
  meta: {
    icon: 'fa fa-fw fa-palette',
    label: trans('color', {}, 'data'),
    description: trans('color_desc', {}, 'data')
  },
  validate: color,
  components: {
    input: ColorInput,
    table: ColorCell
  }
}

export {
  dataType
}
