import React from 'react'
import {useSelector} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'

import {ResourcePage, ResourceEmbedded} from '#/main/core/resource'
import {selectors} from '#/plugin/link/resources/shortcut/store'

const ShortcutPlayer = () => {
  const embeddedResource = useSelector(selectors.embeddedResource)

  return (
    <ResourcePage>
      {!embeddedResource &&
        <ContentPlaceholder
          size="lg"
          title={trans('no_resource', {}, 'resource')}
        />
      }

      {embeddedResource &&
        <ResourceEmbedded
          className="overflow-auto px-4 pt-5"
          resourceNode={embeddedResource}
          showHeader={false}
        />
      }
    </ResourcePage>
  )
}

export {
  ShortcutPlayer
}
