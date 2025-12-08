import React, {useState} from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl'
import {Modal} from '#/main/app/overlays/modal/components/modal'

import {CreationType} from '#/plugin/open-badge/badge/modals/creation/components/type'
import {CreationInfo} from '#/plugin/open-badge/badge/modals/creation/components/info'
import {useSelector} from 'react-redux'
import {selectors as contextSelectors} from '#/main/app/context'
import {selectors} from '#/main/app/platform/store'

const CreationModal = (props) => {
  const [currentStep, setCurrentStep] = useState('type')

  const currentOrganization = useSelector(selectors.currentOrganization)
  const contextType = useSelector(contextSelectors.type)
  const contextData = useSelector(contextSelectors.data)

  const create = () => props.create().then(response => {
    props.fadeModal()

    if (props.onCreate) {
      props.onCreate(response)
    }

    return response
  })

  let StepComponent
  switch (currentStep) {
    case 'type':
      StepComponent = (
        <CreationType
          startCreation={props.startCreation}
          changeStep={setCurrentStep}
          onCreate={props.onCreate}
          fadeModal={props.fadeModal}
        />
      )
      break

    case 'info':
      StepComponent = (
        <CreationInfo
          create={create}
          changeStep={setCurrentStep}
        />
      )
      break
  }

  return (
    <Modal
      {...omit(props, 'create', 'onCreate', 'startCreation', 'reset')}
      title={trans('new_badge', {}, 'badge')}
      subtitle={trans('new_badge_desc', {}, 'badge')}
      centered={true}
      onExited={props.reset}
      onEntering={() => {
        if ('workspace' === contextType) {
          props.startCreation({
            issuer: currentOrganization,
            workspace: contextData
          })
          setCurrentStep('info')
        }
      }}
    >
      {StepComponent}
    </Modal>
  )
}

CreationModal.propTypes = {
  startCreation: T.func.isRequired,
  create: T.func.isRequired,
  onCreate: T.func,
  reset: T.func.isRequired,
  // from modal
  fadeModal: T.func.isRequired
}

export {
  CreationModal
}
