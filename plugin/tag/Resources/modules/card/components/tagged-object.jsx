import React from 'react'
import {PropTypes as T} from 'prop-types'

import {DataCard} from '#/main/app/content/card/components/data'

const TaggedObjectCard = props =>
  <DataCard
    {...props}
    id={props.data.id}
    icon="fa fa-fw fa-tag"
    title={props.data.name}
    subtitle={props.data.type}
  />

TaggedObjectCard.propTypes = {
  className: T.string,
  data: T.shape({

  }).isRequired
}

export {
  TaggedObjectCard
}
