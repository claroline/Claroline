import React from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/core/translation'
import {EmptyPlaceholder} from '#/main/core/layout/components/placeholder'

import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/data/types/resource/prop-types'
import {ResourceCard} from '#/main/core/resource/data/components/resource-card'

// todo placeholder
// todo embedded option
// todo add resource actions

const ResourceDisplay = (props) =>
  <div>
    {!isEmpty(props.data) ?
      <ResourceCard
        data={props.data}
      /> :
      <EmptyPlaceholder
        size="lg"
        icon="fa fa-folder"
        title={trans('no_resource')}
      />}
  </div>


ResourceDisplay.propTypes = {
  data: T.shape(
    ResourceNodeTypes.propTypes
  ).isRequired
}

export {
  ResourceDisplay
}
