import {trans} from '#/main/app/intl/translation'

import {CustomColorDisplay} from '#/main/theme/data/types/customColor/components/display'
import {CustomColorInput} from '#/main/theme/data/types/customColor/components/input'

const dataType = {
  name: 'customColor',
  meta: {
    icon: 'fa fa-fw fa-palette',
    label: trans('custom_color', {}, 'data'),
    description: trans('custom_color_desc', {}, 'data')
  },
  components: {
    display: CustomColorDisplay,
    input: CustomColorInput,
    table: CustomColorDisplay
  }
}

export {
  dataType
}
