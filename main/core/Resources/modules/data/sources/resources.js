
import {ResourceCard} from '#/main/core/resource/data/components/resource-card'

import {trans} from '#/main/core/translation'
import {URL_BUTTON} from '#/main/app/buttons'

export default {
  name: 'resources',
  icon: 'fa fa-fw fa-folder',
  parameters: {
    primaryAction: (resourceNode) => ({ // todo : reuse resource default action
      type: URL_BUTTON,
      target: [ 'claro_resource_show', {
        type: resourceNode.meta.type,
        id: resourceNode.id
      }]
    }),
    definition: [
      {
        name: 'name',
        label: trans('name'),
        displayed: true,
        primary: true
      }, {
        name: 'meta.published',
        type: 'boolean',
        label: trans('published'),
        displayed: true
      }, {
        name: 'meta.created',
        label: trans('creation_date'),
        type: 'date',
        alias: 'creationDate',
        displayed: true
      }, {
        name: 'meta.updated',
        label: trans('modification_date'),
        type: 'date',
        alias: 'modificationDate',
        displayed: true
      }, {
        name: 'resourceType',
        label: trans('type'),
        type: 'string',
        displayable: false,
        filterable: true
      }
    ],
    card: ResourceCard
  }
}
