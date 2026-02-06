import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {UrlButton} from '#/main/app/buttons'
import {DataMicro} from '#/main/app/data/components/micro'

import {route as resourceRoute} from '#/main/core/resource'

const SequenceResources = ({className, resources}) =>
  <div className={classes('list-group', className)}>
    {resources.map(resource =>
      <UrlButton
        key={resource.id}
        className="list-group-item list-group-item-action d-flex align-items-center focus-ring"
        target={'#'+resourceRoute(resource)}
        open="_blank"
      >
        <DataMicro object={resource} />

        <span className="fa fa-arrow-up-right-from-square text-body-tertiary ms-auto" aria-hidden={true} />
      </UrlButton>
    )}
  </div>

SequenceResources.propTypes = {
  className: T.string,
  resources: T.arrayOf(T.shape({
    // resource node type
  })).isRequired
}

export {
  SequenceResources
}
