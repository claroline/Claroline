import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {Await} from '#/main/app/components/await'
import {ContentMeta} from '#/main/app/content/meta/components/meta'

import {getResource} from '#/main/core/resources'
import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'
import {ResourceForm} from '#/main/core/resource/components/form'

import {selectors} from '#/main/core/resource/modals/creation/store'

const ResourceParameters = (props) =>
  <div>
    <ContentMeta
      creator={get(props.resourceNode, 'meta.creator')}
      created={get(props.resourceNode, 'meta.created')}
      updated={get(props.resourceNode, 'meta.updated')}
    />

    <Await
      for={getResource(props.resourceNode.meta.type)}
      then={module => {
        if (module.Creation) {
          const creationApp = module.Creation()

          return React.createElement(creationApp.component)
        }
      }}
    />

    <ResourceForm
      level={5}
      meta={false}
      name={selectors.STORE_NAME}
      dataPart={selectors.FORM_NODE_PART}
    />
  </div>

ResourceParameters.propTypes = {
  resourceNode: T.shape(
    ResourceNodeTypes.propTypes
  ).isRequired
}

export {
  ResourceParameters
}
