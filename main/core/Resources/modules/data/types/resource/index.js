import {trans} from '#/main/app/intl/translation'

import {ResourceCell} from '#/main/core/data/types/resource/components/cell'
import {ResourceFilter} from '#/main/core/data/types/resource/components/filter'
import {ResourceGroup} from '#/main/core/data/types/resource/components/group'
import {ResourceDisplay} from '#/main/core/data/types/resource/components/display'

const dataType = {
  name: 'resource',
  meta: {
    creatable: false,
    icon: 'fa fa-fw fa fa-folder',
    label: trans('resource', {}, 'data'),
    description: trans('resource_desc', {}, 'data')
  },
  // todo : maybe create a validator based on propTypes (would be helpful for this)
  //validate: (value, options) => chain(value, options, [string, match, lengthInRange]),
  components: {
    table: ResourceCell,
    details: ResourceDisplay,
    form: ResourceGroup,
    search: ResourceFilter
  }
}

export {
  dataType
}
