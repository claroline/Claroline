import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Await} from '#/main/app/components/await'
import {getResource} from '#/main/core/resources'

const ResourceMain = (props) =>
  <Await
    for={getResource(props.resourceType)}
    then={module => {
      const resourceApp = module.App()
      if (resourceApp) {
        return React.createElement(resourceApp.component)
      }
    }}
  />

ResourceMain.propTypes = {
  resourceType: T.string.isRequired
}

export {
  ResourceMain
}
