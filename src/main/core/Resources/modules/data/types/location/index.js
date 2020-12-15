import {trans} from '#/main/app/intl/translation'

import {LocationDisplay} from '#/main/core/data/types/location/components/display'
import {LocationInput} from '#/main/core/data/types/location/components/input'

const dataType = {
  name: 'location',
  meta: {
    creatable: false,
    icon: 'fa fa-fw fa fa-location-arrow',
    label: trans('location', {}, 'data'),
    description: trans('location_desc', {}, 'data')
  },
  render: (raw) => raw ? raw.name : null,
  components: {
    details: LocationDisplay,
    input: LocationInput
  }
}

export {
  dataType
}
