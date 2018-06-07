import React from 'react'

import {trans} from '#/main/core/translation'
import {MODAL_SELECTION} from '#/main/app/modals/selection'

import {getType} from '#/main/core/resource/utils'
import {ResourceIcon} from '#/main/core/resource/components/icon'

const action = (resourceNodes) => ({
  name: 'add',
  type: 'modal',
  label: trans('add', {}, 'actions'),
  icon: 'fa fa-fw fa-plus',
  primary: true,
  modal: [MODAL_SELECTION, {
    icon: 'fa fa-fw fa-plus',
    title: trans('create_resource'),
    items: resourceNodes[0].permissions.create.map(name => {
      const tags = getType({meta: {type: name}}).tags || []

      return ({ // todo maybe filter disabled types
        icon: React.createElement(ResourceIcon, {
          mimeType: `custom/${name}`
        }),
        label: trans(name, {}, 'resource'),
        description: trans(`${name}_desc`, {}, 'resource'),
        tags: tags.map(tag => trans(tag))
      })
    }),
    handleSelect: () => {

    }
  }]
})

export {
  action
}
