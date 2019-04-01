import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'

import {theme} from '#/main/app/config'
import {Await} from '#/main/app/components/await'

import {getResource} from '#/main/core/resources'
import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'
import {ResourceForm} from '#/main/core/resource/components/form'

import {selectors} from '#/main/core/resource/modals/creation/store'

const ResourceParameters = (props) =>
  <ResourceForm
    level={5}
    meta={true}
    name={selectors.STORE_NAME}
    dataPart={selectors.FORM_NODE_PART}
  >
    <Await
      for={getResource(props.resourceNode.meta.type)}
      then={module => {
        if (module.Creation) {
          const creationApp = module.Creation()

          return (
            <Fragment>
              {React.createElement(creationApp.component)}

              {creationApp.styles && creationApp.styles.map(styleName =>
                <link key={styleName} rel="stylesheet" type="text/css" href={theme(styleName)} />
              )}
            </Fragment>
          )
        }
      }}
    />
  </ResourceForm>

ResourceParameters.propTypes = {
  resourceNode: T.shape(
    ResourceNodeTypes.propTypes
  ).isRequired
}

export {
  ResourceParameters
}
