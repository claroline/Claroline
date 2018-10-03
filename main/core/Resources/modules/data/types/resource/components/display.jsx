import React from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/core/translation'
import {EmptyPlaceholder} from '#/main/core/layout/components/placeholder'

import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'
import {ResourceCard} from '#/main/core/resource/data/components/resource-card'

// todo embedded option
// todo add resource actions

const ResourceDisplay = (props) => {
  if (!isEmpty(props.data)) {
    return (
      <ResourceCard
        data={props.data}
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

ResourceDisplay.propTypes = {
  data: T.shape(
    ResourceNodeTypes.propTypes
  ).isRequired
}

export {
  ResourceDisplay
}
