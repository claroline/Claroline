import React from 'react'
import {PropTypes as T} from 'prop-types'

import {asset} from '#/main/app/config/asset'
import {DataCard} from '#/main/app/content/card/components/data'

import {Course as CourseType} from '#/plugin/cursus/administration/cursus/prop-types'

const CourseCard = props =>
  <DataCard
    {...props}
    id={props.data.id}
    icon="fa fa-tasks"
    title={props.data.title}
    subtitle={props.data.code}
    poster={props.data.meta.icon ? asset(props.data.meta.icon) : null}
    contentText={props.data.description}
  />

CourseCard.propTypes = {
  data: T.shape(CourseType.propTypes).isRequired
}

export {
  CourseCard
}
