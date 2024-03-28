import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {Routes} from '#/main/app/router'
import {Await} from '#/main/app/components/await'
import {ContentLoader} from '#/main/app/content/components/loader'

import {getIntegrations} from '#/main/core/integration'
import {Tool} from '#/main/core/tool'

const IntegrationTool = props =>
  <Tool
    {...props}
    styles={['claroline-distribution-main-core-administration-integration']}
  >
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
  </Tool>

IntegrationTool.propTypes = {
  path: T.string.isRequired
}

export {
  IntegrationTool
}