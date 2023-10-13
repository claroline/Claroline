import React from 'react'

import {trans} from '#/main/app/intl'
import {ContentForbidden} from '#/main/app/content/components/forbidden'

const DesktopForbidden = () =>
  <ContentForbidden
    size="lg"
    title={trans('access_forbidden', {}, 'desktop')}
    description={trans('access_forbidden_help', {}, 'desktop')}
  />

export {
  DesktopForbidden
}
