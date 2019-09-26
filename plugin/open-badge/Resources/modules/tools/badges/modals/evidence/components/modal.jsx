import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {FormData} from '#/main/app/content/form/containers/data'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action/components/button'
import {ResourceCard} from '#/main/core/resource/components/card'

import {selectors} from '#/plugin/open-badge/tools/badges/modals/evidence/store/selectors'

const EvidenceModal = props =>
  <Modal
    {...props}
    icon="fa fa-fw fa-cog"
    title={trans('evidence', {}, 'badge')}
    subtitle={props.assertion.badge.name}
    onEntering={() => props.initForm(props.evidence)}
  >
    <FormData
      {...props}
      name={selectors.STORE_NAME}
      meta={false}
      buttons={false}
      target={['apiv2_evidence_create']}
      sections={[
        {
          title: trans('evidence', {}, 'badge'),
          primary: true,
          fields: [
            {
              name: 'name',
              type: 'string',
              label: trans('name'),
              required: true
            },
            {
              name: 'narrative',
              type: 'html',
              label: trans('narrative', {}, 'badge'),
              required: true,
              options: {
                long: true
              }
            }
          ]
        }
      ]}
    >
    </FormData>
    {props.evidence.resource &&
      <ResourceCard data={props.evidence.resource}/> 
    }
    <Button
      className="btn"
      style={{marginTop: 10}}
      type={CALLBACK_BUTTON}
      icon="fa fa-fw fa-save"
      label={trans('save')}
      primary={true}
      callback={() => {
        props.saveEvidence(props.assertion)
        props.fadeModal()
      }}
    />
  </Modal>

EvidenceModal.propTypes = {
  fadeModal: T.func.isRequired,
  saveEvidence: T.func.isRequired,
  initForm: T.func.isRequired,
  assertion: T.object,
  evidence: T.object
}

export {
  EvidenceModal
}
