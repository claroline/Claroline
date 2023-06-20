import React from 'react'
import {PropTypes as T} from 'prop-types'

import omit from 'lodash/omit'
import {trans} from '#/main/app/intl/translation'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {selectors} from '#/main/privacy/modals/terms/store/selectors'
import {DetailsData} from '#/main/app/content/details/components/data'

const TermsModal = (props) => {
  console.log(props)
  return(
    <Modal
      {...omit(props, 'formData', 'reset', 'termsOfService', 'fadeModal')}
      icon="fa fa-fw fa-regular fa-eye"
      title={trans('terms_of_service', {}, 'privacy')}
      onEntering={() => props.reset(props.termsOfService, props.termsOfServiceEnabled)}
    >
      <DetailsData
        name={selectors.STORE_NAME}
        definition={[
          {
            title: trans('general'),
            primary: true,
            fields: [
              {
                name: 'termsOfService',
                type: 'string',
                label: trans('terms_of_service', {}, 'privacy'),
                options: {long: true}
              }
            ]
          }
        ]}
      >
      </DetailsData>
    </Modal>
  )
}

TermsModal.propTypes = {
  formData: T.object.isRequired,
  fadeModal: T.func,
  termsOfService: T.object,
  reset: T.func.isRequired
}

export {
  TermsModal
}