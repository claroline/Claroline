import {trans} from '#/main/app/intl/translation'

import {LocationDisplay} from '#/main/core/data/types/location/components/display'
import {LocationGroup} from '#/main/core/data/types/location/components/group'

const dataType = {
  name: 'location',
  meta: {
    creatable: false,
    icon: 'fa fa-fw fa fa-location-arrow',
    label: trans('location'),
    description: trans('location_desc')
  },
  components: {
    details: LocationDisplay,
    form: LocationGroup
  }
}

export {
  dataType
}
