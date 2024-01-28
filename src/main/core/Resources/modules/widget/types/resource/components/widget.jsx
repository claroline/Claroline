import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'

import {ResourceEmbedded} from '#/main/core/resource/containers/embedded'
import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'

const ResourceWidget = props => {
  if (props.resourceNode) {
    return (
      <ResourceEmbedded
        className="widget-resource row"
        resourceNode={props.resourceNode}
        showHeader={props.showResourceHeader}
      />
    )
  }

  return (
    <ContentPlaceholder
      size="lg"
      icon="fa fa-folder"
      title={trans('no_resource', {}, 'resource')}
    />
  )
}

ResourceWidget.propTypes = {
  resourceNode: T.shape(
    ResourceNodeTypes.propTypes
  ),
  showResourceHeader: T.bool
}

export {
  ResourceWidget
}
