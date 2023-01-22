import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {Routes} from '#/main/app/router'
import {Await} from '#/main/app/components/await'
import {ContentLoader} from '#/main/app/content/components/loader'

import {getIntegrations} from '#/main/core/integration'

const IntegrationTool = props =>
  <Await
    for={getIntegrations()}
    placeholder={
      <ContentLoader
        size="lg"
        description={trans('loading', {}, 'tools')}
      />
    }
    then={(apps) => (
      <Routes
        path={props.path}
        redirect={[
          {from: '/', exact: true, to: `/${apps[0].default.name}`}
        ]}
        routes={apps.map(app => ({
          path: `/${app.default.name}`,
          component: app.default.component
        }))}
      />
    )}
  />

IntegrationTool.propTypes = {
  path: T.string.isRequired
}

export {
  IntegrationTool
}