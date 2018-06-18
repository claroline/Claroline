import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/core/translation'

import {ResourceIcon} from '#/main/core/resource/components/icon'

/**
 * Renders the name and icon of a ResourceType.
 */
const ResourceType = props =>
  <article className="resource-type">
    <ResourceIcon mimeType={props.mimeType} />

    <div>
      <h1>{trans(props.name, {}, 'resource')}</h1>
      <p className="hidden-xs">{trans(`${props.name}_desc`, {}, 'resource')}</p>
    </div>
  </article>

ResourceType.propTypes = {
  mimeType: T.string,
  name: T.string
}

ResourceType.defaultProps = {
  mimeType: 'custom/default',
  name: 'unknown'
}

export {
  ResourceType
}
