import React, {Fragment, useState} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {ContentTabs} from '#/main/app/content/components/tabs'

import {Course as CourseTypes} from '#/plugin/cursus/prop-types'
import {CourseParameters} from '#/plugin/cursus/course/components/parameters'
import {CourseRegistration} from '#/plugin/cursus/course/components/registration'

const CourseForm = (props) => {
  const [currentSection, setSection] = useState('parameters')

  return (
    <Fragment>
      <ContentTabs
        className="content-md"
        sections={[
          {
            name: 'parameters',
            type: CALLBACK_BUTTON,
            icon: 'fa fa-fw fa-cog',
            label: trans('parameters'),
            callback: () => setSection('parameters'),
            active: 'parameters' === currentSection
          }, {
            name: 'registration',
            type: CALLBACK_BUTTON,
            icon: 'fa fa-fw fa-user-plus',
            label: trans('registration'),
            callback: () => setSection('registration'),
            active: 'registration' === currentSection
          }
        ]}
      />

      {'parameters' === currentSection &&
        <CourseParameters {...props} />
      }

      {'registration' === currentSection &&
        <CourseRegistration {...props} />
      }
    </Fragment>
  )
}

CourseForm.propTypes = {
  path: T.string.isRequired,
  name: T.string.isRequired,

  // from store
  isNew: T.bool.isRequired,
  course: T.shape(
    CourseTypes.propTypes
  ),
  save: T.func.isRequired,
  update: T.func.isRequired
}

export {
  CourseForm
}
