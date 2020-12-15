import {trans} from '#/main/app/intl/translation'

import {IconInput} from '#/main/theme/data/types/icon/components/input'
import {IconDisplay} from '#/main/theme/data/types/icon/components/display'

const dataType = {
  name: 'icon',
  meta: {
    icon: 'fa fa-fw fa-icons',
    label: trans('icon', {}, 'data'),
    description: trans('icon_desc', {}, 'data')
  },

  components: {
    display: IconDisplay,
    input: IconInput,
    table: IconDisplay
  }
}

export {
  dataType
}
