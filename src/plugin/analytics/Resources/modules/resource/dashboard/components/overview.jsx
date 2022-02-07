import React, {Fragment, createElement} from 'react'
import {PropTypes as T} from 'prop-types'
import isUndefined from 'lodash/isUndefined'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {Await} from '#/main/app/components/await'
import {ContentTitle} from '#/main/app/content/components/title'
import {ContentLoader} from '#/main/app/content/components/loader'

import {getResourceAnalytics} from '#/plugin/analytics/utils'
import {DashboardResume} from '#/plugin/analytics/resource/dashboard/components/resume'

const DashboardOverview = (props) =>
  <Fragment>
    <ContentTitle
      title={trans('overview', {}, 'analytics')}
    />

    <DashboardResume
      resourceNode={props.resourceNode}
    />

    <Await
      for={getResourceAnalytics(props.resourceNode).then(apps => apps.filter(app => !isUndefined(get(app, 'components.overview'))))}
      placeholder={
        <ContentLoader
          className="row"
          size="lg"
          description={trans('loading', {}, 'analytics')}
        />
      }
      then={(apps) => apps.map((app) => createElement(get(app, 'components.overview')))}
    />
  </Fragment>

DashboardOverview.propTypes = {
  resourceNode: T.shape({
    id: T.string.isRequired
  }).isRequired
}

export {
  DashboardOverview
}