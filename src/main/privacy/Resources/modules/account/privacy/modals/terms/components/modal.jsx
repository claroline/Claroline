import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {selectors} from '#/main/privacy/administration/privacy/store'
import {DetailsData} from '#/main/app/content/details/containers/data'

const TermsOfServiceModal = (props) =>
  <Modal
    {...props}
    icon="fa fa-fw fa-circle-info"
    title={trans('terms_of_service', {}, 'privacy')}
  >
    <DetailsData
      name={selectors.FORM_NAME}
      sections={[
        {
          icon: 'fa fa-fw fa-copyright',
          title: trans('terms_of_service', {}, 'privacy'),
          fields: [
            {
              name: 'tos.text',
              type: 'translated'
            }
          ]
        }
      ]}
    />
  </Modal>

export {
  TermsOfServiceModal
}