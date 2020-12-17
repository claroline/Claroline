import {trans} from '#/main/app/intl/translation'

import {ScaleDisplay} from '#/plugin/competency/data/types/scale/components/display'
import {ScaleInput} from '#/plugin/competency/data/types/scale/components/input'

const dataType = {
  name: 'competency_scale',
  meta: {
    icon: 'fa fa-fw fa fa-arrow-up',
    label: trans('scale', {}, 'data'),
    description: trans('scale_desc', {}, 'data')
  },
  components: {
    details: ScaleDisplay,
    input: ScaleInput
  }
}

export {
  dataType
}
