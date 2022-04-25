import React from 'react'
import {PropTypes as T} from 'prop-types'

import {transChoice} from '#/main/app/intl/translation'
import {DataCard} from '#/main/app/data/components/card'

import {Tag as TagTypes} from '#/plugin/tag/data/types/tag/prop-types'

const TagCard = props =>
  <DataCard
    {...props}
    id={props.data.id}
    icon="fa fa-fw fa-tag"
    title={props.data.name}
    color={props.data.color}
    subtitle={transChoice('count_elements', props.data.elements, {count: props.data.elements})}
    contentText={props.data.meta.description}
  />

TagCard.propTypes = {
  className: T.string,
  data: T.shape(
    TagTypes.propTypes
  ).isRequired
}

export {
  TagCard
}
