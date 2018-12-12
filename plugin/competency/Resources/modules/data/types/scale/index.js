import {trans} from '#/main/app/intl/translation'

import {ScaleDisplay} from '#/plugin/competency/data/types/scale/components/display'
import {ScaleGroup} from '#/plugin/competency/data/types/scale/components/group'

const dataType = {
  name: 'competency_scale',
  meta: {
    creatable: false,
    icon: 'fa fa-fw fa fa-arrow-up',
    label: trans('scale', {}, 'competency'),
    description: trans('scale_desc', {}, 'competency')
  },
  components: {
    details: ScaleDisplay,
    form: ScaleGroup
  }
}

export {
  dataType
}
