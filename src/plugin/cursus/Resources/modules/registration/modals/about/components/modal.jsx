import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {DetailsData} from '#/main/app/content/details/components/data'
import {hasPermission} from '#/main/app/security'
import {formatSections} from '#/main/app/content/form/parameters/utils'

import {Course as CourseTypes} from '#/plugin/cursus/prop-types'

const AboutModal = props => {
  const isManager = hasPermission('edit', props.course)
  let allFields = []
  get(props.course, 'registration.form', []).map(section => {
    allFields = allFields.concat(section.fields)
  })

  const sections = formatSections(get(props.course, 'registration.form', []), allFields, 'data', true, isManager, isManager)

  return (
    <Modal
      {...omit(props, 'registration')}
      icon="fa fa-fw fa-circle-info"
      title={trans('about')}
    >
      <DetailsData
        flush={true}
        data={props.registration}
        definition={[
          {
            title: trans('general'),
            primary: true,
            fields: [
              {
                name: 'user',
                type: 'user',
                label: trans('user')
              }, {
                name: 'date',
                type: 'date',
                label: trans('registration_date', {}, 'cursus'),
                options: {time: true}
              }
            ]
          }
        ].concat(sections)}
      />
    </Modal>
  )
}
AboutModal.propTypes = {
  course: T.shape(
    CourseTypes.propTypes
  ).isRequired,
  registration: T.object.isRequired
}

export {
  AboutModal
}
