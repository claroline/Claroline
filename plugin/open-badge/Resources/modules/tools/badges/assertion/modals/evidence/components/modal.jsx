import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {FormData} from '#/main/app/content/form/containers/data'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action/components/button'
import {ResourceCard} from '#/main/core/resource/components/card'

import {selectors} from '#/plugin/open-badge/tools/badges/assertion/modals/evidence/store/selectors'

const EvidenceModal = props =>
  <Modal
    {...omit(props, 'assertion', 'evidence', 'isNew', 'saveEvidence', 'initForm')}
    icon="fa fa-fw fa-cog"
    title={trans('evidence', {}, 'badge')}
    subtitle={props.assertion.badge.name}
    onEntering={() => props.initForm(props.evidence)}
  >
    <FormData
      name={selectors.STORE_NAME}
      target={['apiv2_evidence_create']}
      sections={[
        {
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'name',
              type: 'string',
              label: trans('name'),
              required: true
            }, {
              name: 'narrative',
              type: 'html',
              label: trans('narrative', {}, 'badge'),
              required: true,
              options: {
                long: true
              }
            }, {
              name: 'resource',
              type: 'resource',
              label: trans('resource')
            }
          ]
        }
      ]}
    />

    <Button
      className="btn modal-btn"
      type={CALLBACK_BUTTON}
      label={trans('save', {}, 'actions')}
      primary={true}
      callback={() => {
        props.saveEvidence(props.assertion)
        props.fadeModal()
      }}
    />
  </Modal>

EvidenceModal.propTypes = {
  assertion: T.object,
  evidence: T.object,

  // from store
  isNew: T.bool.isRequired,
  saveEvidence: T.func.isRequired,
  initForm: T.func.isRequired,

  // from modal
  fadeModal: T.func.isRequired
}

export {
  EvidenceModal
}
