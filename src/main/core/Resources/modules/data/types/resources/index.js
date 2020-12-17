import {trans} from '#/main/app/intl/translation'

import {ResourcesInput} from '#/main/core/data/types/resources/components/input'
import {ResourcesDisplay} from '#/main/core/data/types/resources/components/display'

const dataType = {
  name: 'resources',
  meta: {
    creatable: false,
    icon: 'fa fa-fw fa fa-folder',
    label: trans('resources', {}, 'data'),
    description: trans('resources_desc', {}, 'data')
  },
  components: {
    details: ResourcesDisplay,
    input: ResourcesInput
  }
}

export {
  dataType
}
