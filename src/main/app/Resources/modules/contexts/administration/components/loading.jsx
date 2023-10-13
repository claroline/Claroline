import React from 'react'

import {trans} from '#/main/app/intl'
import {ContentLoader} from '#/main/app/content/components/loader'

const AdministrationLoading = () =>
  <ContentLoader
    size="lg"
    description={trans('loading', {}, 'administration')}
  />

export {
  AdministrationLoading
}
