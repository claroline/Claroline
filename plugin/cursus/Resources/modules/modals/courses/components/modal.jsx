import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {ListData} from '#/main/app/content/list/containers/data'

import {CourseCard} from '#/plugin/cursus/course/components/card'
import {Course as CourseTypes} from '#/plugin/cursus/course/prop-types'

import {selectors} from '#/plugin/cursus/modals/courses/store'

const CoursesModal = props => {
  const selectAction = props.selectAction(props.selected)

  return (
    <Modal
      {...omit(props, 'url', 'selected', 'selectAction', 'reset')}
      icon="fa fa-fw fa-graduation-cap"
      className="data-picker-modal"
      bsSize="lg"
      onExiting={props.reset}
    >
      <ListData
        name={selectors.STORE_NAME}
        fetch={{
          url: props.url,
          autoload: true
        }}
        definition={[
          {
            name: 'name',
            type: 'string',
            label: trans('name'),
            displayed: true,
            primary: true
          }, {
            name: 'code',
            type: 'string',
            label: trans('code'),
            displayed: true
          }
        ]}
        card={CourseCard}
      />

      <Button
        label={trans('select', {}, 'actions')}
        {...selectAction}
        className="modal-btn btn"
        primary={true}
        disabled={0 === props.selected.length}
        onClick={props.fadeModal}
      />
    </Modal>
  )
}

CoursesModal.propTypes = {
  url: T.oneOfType([T.string, T.array]),
  title: T.string,
  selectAction: T.func.isRequired,
  fadeModal: T.func.isRequired,

  // from store
  selected: T.arrayOf(T.shape(CourseTypes.propTypes)).isRequired,
  reset: T.func.isRequired
}

CoursesModal.defaultProps = {
  url: ['apiv2_cursus_course_list'],
  title: trans('courses', {}, 'cursus')
}

export {
  CoursesModal
}
