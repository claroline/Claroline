import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'

import {route} from '#/main/core/resource/routing'
import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'
import {ResourceCard} from '#/main/core/resource/components/card'

const ResourcesDisplay = (props) => {
  if (!isEmpty(props.data)) {
    return (
      <Fragment>
        {props.data.map(resource =>
          <ResourceCard
            key={`resource-card-${resource.id}`}
            data={resource}
            primaryAction={{
              type: LINK_BUTTON,
              label: trans('open', {}, 'actions'),
              target: route(resource)
            }}
            size="xs"
          />
        )}
      </Fragment>
    )
  }

  return (
    <ContentPlaceholder
      icon="fa fa-folder"
      title={trans('no_resource', {}, 'resource')}
    />
  )
}

ResourcesDisplay.propTypes = {
  data: T.arrayOf(T.shape(
    ResourceNodeTypes.propTypes
  )).isRequired
}

export {
  ResourcesDisplay
}
