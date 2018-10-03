import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/core/translation'
import {EmptyPlaceholder} from '#/main/core/layout/components/placeholder'

import {ResourceEmbedded} from '#/main/core/resource/components/embedded'
import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'

const ResourceWidget = props => {
  if (props.resourceNode) {
    return (
      <ResourceEmbedded
        className="widget-resource"
        resourceNode={props.resourceNode}
        showHeader={props.showResourceHeader}
      />
    )
  }

  return (
    <EmptyPlaceholder
      size="lg"
      icon="fa fa-folder"
      title={trans('no_resource')}
    />
  )
}

ResourceWidget.propTypes = {
  resourceNode: T.shape(
    ResourceNodeTypes.propTypes
  ),
  showResourceHeader: T.bool.isRequired
}

export {
  ResourceWidget
}
