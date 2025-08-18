import React from 'react'
import {PropTypes as T} from 'prop-types'
import {useSelector} from 'react-redux'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {FormModal} from '#/main/app/data/modals/form/components/modal'

import {selectors as formSelectors} from '#/main/app/content/form'

const STORE_NAME = 'transferBadgesForm'

const TransferModal = (props) => {
  const formData = useSelector((state) => formSelectors.data(formSelectors.form(state, STORE_NAME)))

  return (
    <FormModal
      {...props}
      name={STORE_NAME}
      title={trans('transfer_badges', {}, 'actions')}
      isNew={true}
      target={['apiv2_badge_assertion_transfer', {
        userFrom: get(formData, 'userFrom.id'),
        userTo: get(formData, 'userTo.id')
      }]}
      saveLabel={trans('transfer', {}, 'actions')}
      definition={[
        {
          id: 'general',
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'userFrom',
              type: 'user',
              label: trans('transfer_from', {}, 'badge')
            }, {
              name: 'userTo',
              type: 'user',
              label: trans('transfer_to', {}, 'badge')
            }
          ]
        }
      ]}
    />
  )
}

TransferModal.propTypes = {
  fadeModal: T.func.isRequired
}

export {
  TransferModal
}
