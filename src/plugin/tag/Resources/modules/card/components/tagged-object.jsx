import React from 'react'
import {PropTypes as T} from 'prop-types'

import {DataCard} from '#/main/app/data/components/card'

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
    id: T.string.isRequired,
    name: T.string,
    type: T.string
  }).isRequired
}

export {
  TaggedObjectCard
}
