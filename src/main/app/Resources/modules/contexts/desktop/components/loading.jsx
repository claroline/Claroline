import React from 'react'

import {trans} from '#/main/app/intl'
import {ContentLoader} from '#/main/app/content/components/loader'

const DesktopLoading = () =>
  <ContentLoader
    className="app-page-loader"
    size="lg"
    description={trans('loading', {}, 'desktop')}
  />

export {
  DesktopLoading
}
