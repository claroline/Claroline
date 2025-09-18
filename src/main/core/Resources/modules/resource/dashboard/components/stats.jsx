import React from 'react'
import {PropTypes as T} from 'prop-types'

import {PageContent} from '#/main/app/page'

const ResourceDashboardStats = (props) => {
  return (
    <PageContent className="py-4">
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
