import React from 'react'
import {useSelector} from 'react-redux'

import {ResourcePage, selectors as resourceSelectors} from '#/main/core/resource'

import {UrlDisplay} from '#/plugin/url/components/display'
import {selectors} from '#/plugin/url/resources/url/store'
import {PageContent} from '#/main/app/page'

const UrlPlayer = () => {
  const url = useSelector(selectors.url)
  const embedded = useSelector(resourceSelectors.embedded)

  return (
    <ResourcePage>
      <PageContent>
        <UrlDisplay
          url={url.url}
          mode={url.mode}
          ratio={embedded ? url.ratio : undefined}
        />
      </PageContent>
    </ResourcePage>
  )
}

export {
  UrlPlayer
}
