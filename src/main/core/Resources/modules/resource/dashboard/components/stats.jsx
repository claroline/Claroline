import React from 'react'
import {PropTypes as T} from 'prop-types'

import {PageContent, PageHeading} from '#/main/app/page'
import {trans} from '#/main/app/intl'

const ResourceDashboardStats = (props) => {
  return (
    <PageContent className="py-4">
      <PageHeading title={trans('statistics')} className="visually-hidden" level={2} />
      {props.children}
    </PageContent>
  )
}

ResourceDashboardStats.propTypes = {
  children: T.any
}

export {
  ResourceDashboardStats
}
