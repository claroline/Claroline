import React from 'react'

import {trans} from '#/main/app/intl'
import {ContentLoader} from '#/main/app/content/components/loader'

const WorkspaceLoading = () =>
  <ContentLoader
    size="lg"
    description={trans('loading', {}, 'workspace')}
  />

export {
  WorkspaceLoading
}
