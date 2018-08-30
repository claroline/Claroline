import {trans} from '#/main/core/translation'

import {ResourceGroup} from '#/main/core/resource/data/types/resource/components/group'
import {ResourceDisplay} from '#/main/core/resource/data/types/resource/components/display'

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
    details: ResourceDisplay,
    form: ResourceGroup
  }
}

export {
  dataType
}
