import React from 'react'
import {PropTypes as T} from 'prop-types'

export const ResourceContentThumbnail = () =>
  <div className="resource-content-thumbnail">
  </div>

ResourceContentThumbnail.propTypes = {
  resource: T.object,
  type: T.string.isRequired
}
