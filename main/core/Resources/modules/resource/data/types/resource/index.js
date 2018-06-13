import {registerType} from '#/main/core/data'

import {trans} from '#/main/core/translation'
//import {chain, lengthInRange, match, string} from '#/main/core/validation'

import {ResourceGroup} from '#/main/core/resource/data/types/resource/components/group'
import {ResourceDisplay} from '#/main/core/resource/data/types/resource/components/display'

const RESOURCE_TYPE = 'resource'

const resourceDefinition = {
  meta: {
    type: RESOURCE_TYPE,
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

registerType(RESOURCE_TYPE, resourceDefinition)

export {
  RESOURCE_TYPE
}
