import React from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {DetailsData} from '#/main/app/content/details/components/data'
import {hasPermission} from '#/main/app/security'
import {formatSections} from '#/main/app/content/form/parameters/utils'

const AboutModal = props => {
  let sections = []
  if (!isEmpty(props.registration.form)) {
    const isManager = hasPermission('administrate', props.registration)
    let allFields = []
    props.registration.form.map(section => {
      allFields = allFields.concat(section.fields)
    })

    sections = formatSections(props.registration.form, allFields, 'data', true, isManager, isManager)
  }

  return (
    <Modal
      {...omit(props, 'registration')}
      title={trans('registration')}
    >
      <div className="modal-body" role="presentation">
        <DetailsData
          className="mb-3"
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
                  label: trans('registration_date'),
                  options: {time: true}
                }
              ]
            }
          ].concat(sections)}
        />
      </div>
    </Modal>
  )
}
AboutModal.propTypes = {
  registration: T.object.isRequired
}

export {
  AboutModal
}
