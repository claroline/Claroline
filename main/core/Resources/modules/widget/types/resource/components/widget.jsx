import React from 'react'
import {PropTypes as T} from 'prop-types'

import {ResourceEmbedded} from '#/main/core/resource/components/embedded'
import {ResourceNode as resourceNodeTypes} from '#/main/core/resource/data/types/resource/prop-types'

const ResourceWidget = props =>
  <ResourceEmbedded
    resourceNode={props.resourceNode}
  />

ResourceWidget.propTypes = {
  resourceNode: T.shape(resourceNodeTypes.propTypes).isRequired
}

export {
  ResourceWidget
}
