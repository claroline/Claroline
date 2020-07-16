import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'

import {Course as CourseTypes} from '#/plugin/cursus/course/prop-types'
import {CourseCard} from '#/plugin/cursus/course/components/card'

const CourseDisplay = (props) => props.data ?
  <CourseCard
    data={props.data}
    size="xs"
  /> :
  <ContentPlaceholder
    icon="fa fa-graduation-cap"
    title={trans('no_course', {}, 'cursus')}
  />

CourseDisplay.propTypes = {
  data: T.arrayOf(T.shape(
    CourseTypes.propTypes
  ))
}

export {
  CourseDisplay
}
