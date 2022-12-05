import React, {Fragment} from 'react'

import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action/components/button'

import {trans} from '#/main/app/intl/translation'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'
import {CourseCard} from '#/plugin/cursus/course/components/card'
import {Course as CourseTypes} from '#/plugin/cursus/prop-types'
import {MODAL_COURSES} from '#/plugin/cursus/modals/courses'

const CourseButton = props =>
  <Button
    className="btn btn-block"
    style={{marginTop: 10}}
    type={MODAL_BUTTON}
    icon="fa fa-fw fa-plus"
    label={trans('add_course', {}, 'cursus')}
    disabled={props.disabled}
    modal={[MODAL_COURSES, {
      title: props.title,
      selectAction: (selected) => ({
        type: CALLBACK_BUTTON,
        label: trans('select', {}, 'actions'),
        callback: () => props.onChange(selected[0])
      })
    }]}
    size={props.size}
  />

CourseButton.propTypes = {
  title: T.string,
  disabled: T.bool,
  onChange: T.func.isRequired,
  size: T.string
}

const CourseInput = props => {
  if (props.value) {
    return (
      <Fragment>
        <CourseCard
          data={props.value}
          size="xs"
          actions={[
            {
              name: 'delete',
              type: CALLBACK_BUTTON,
              icon: 'fa fa-fw fa-trash',
              label: trans('delete', {}, 'actions'),
              dangerous: true,
              disabled: props.disabled,
              callback: () => props.onChange(null)
            }
          ]}
        />

        <CourseButton
          {...props.picker}
          disabled={props.disabled}
          onChange={props.onChange}
          size={props.size}
        />
      </Fragment>
    )
  }

  return (
    <ContentPlaceholder
      icon="fa fa-graduation-cap"
      title={trans('no_course')}
      size={props.size}
    >
      <CourseButton
        {...props.picker}
        size={props.size}
        disabled={props.disabled}
        onChange={props.onChange}
      />
    </ContentPlaceholder>
  )
}

implementPropTypes(CourseInput, DataInputTypes, {
  value: T.shape(
    CourseTypes.propTypes
  ),
  picker: T.shape({
    title: T.string
  })
}, {
  value: null
})

export {
  CourseInput
}
