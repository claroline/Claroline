import React from 'react'
import {useSelector} from 'react-redux'

import {trans} from '#/main/app/intl'
import {LINK_BUTTON} from '#/main/app/buttons'
import {Resource, route} from '#/main/core/resource'

import {ShortcutPlayer} from '#/plugin/link/resources/shortcut/components/player'
import {ShortcutEditor} from '#/plugin/link/resources/shortcut/components/editor'
import {selectors} from '#/plugin/link/resources/shortcut/store'

const ShortcutResource = (props) => {
  const embeddedResource = useSelector(selectors.embeddedResource)

  return (
    <Resource
      {...props}
      editor={ShortcutEditor}
      pages={[
        {
          path: '/',
          component: ShortcutPlayer,
          exact: true
        }
      ]}
      actions={[
        {
          name: 'open-resource',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-arrow-up-right-from-square',
          label: trans('open-resource', {}, 'actions'),
          target: embeddedResource ? route(embeddedResource) : '',
          displayed: !!embeddedResource
        }
      ]}
    />
  )
}

export {
  ShortcutResource
}
