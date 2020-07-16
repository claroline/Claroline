import React from 'react'
import {PropTypes as T} from 'prop-types'

import {asset} from '#/main/app/config/asset'
import {DataCard} from '#/main/app/data/components/card'

import {Course as CourseTypes} from '#/plugin/cursus/course/prop-types'

const CourseCard = props =>
  <DataCard
    {...props}
    id={props.data.id}
    poster={props.data.thumbnail ? asset(props.data.thumbnail.url) : null}
    icon="fa fa-graduation-cap"
    title={props.data.name}
    subtitle={props.data.code}
    contentText={props.data.description}
  />

CourseCard.propTypes = {
  data: T.shape(
    CourseTypes.propTypes
  ).isRequired
}

export {
  CourseCard
}
