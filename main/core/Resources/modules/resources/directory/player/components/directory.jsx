import React from 'react'

import {trans} from '#/main/core/translation'
import {URL_BUTTON} from '#/main/app/buttons'
import {ResourceExplorer} from '#/main/core/resource/explorer/containers/explorer'

import {selectors} from '#/main/core/resources/directory/player/store'

// TODO : fix reloading at resource creation

const DirectoryPlayer = () =>
  <ResourceExplorer
    name={selectors.EXPLORER_NAME}
    primaryAction={(resourceNode) => ({ // todo : use resource default action
      type: URL_BUTTON,
      label: trans('open', {}, 'actions'),
      target: [ 'claro_resource_show', {
        type: resourceNode.meta.type,
        id: resourceNode.id
      }]
    })}
  />

export {
  DirectoryPlayer
}
