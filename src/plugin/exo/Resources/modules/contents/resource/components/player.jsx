import React from 'react'
import {PropTypes as T} from 'prop-types'

import {ResourceEmbedded} from '#/main/core/resource/containers/embedded'

export const ResourceContentPlayer = (props) =>
  <ResourceEmbedded
    resourceNode={props.item.resource}
  />

ResourceContentPlayer.propTypes = {
  item: T.shape({
    id: T.string.isRequired,
    type: T.string.isRequired,
    resource: T.object.isRequired
  }).isRequired
}
