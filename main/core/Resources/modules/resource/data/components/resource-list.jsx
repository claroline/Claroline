import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'

import {ResourceCard} from '#/main/core/resource/data/components/resource-card'

const ResourceList = {
  open: () => ({
    type: LINK_BUTTON,
    target: '/#',
    label: trans('open', {}, 'actions')
  }),
  definition: [
    {
      name: 'name',
      type: 'string',
      label: trans('name'),
      displayed: true,
      primary: true
    }
  ],
  card: ResourceCard
}

export {
  ResourceList
}
