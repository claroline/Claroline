import React from 'react'
import {useSelector} from 'react-redux'

import {trans} from '#/main/app/intl'

import {ResourcePage} from '#/main/core/resource/components/page'
import {selectors as resourceSelectors} from '#/main/core/resource/store'

/**
 * Display the User progression in a Resource.
 */
const ResourceProgression = () => {
  const userEvaluation = useSelector(resourceSelectors.resourceEvaluation)

  return (
    <ResourcePage
      title={trans('my_progression')}
    >
      Ma progression
    </ResourcePage>
  )
}

export {
  ResourceProgression
}
