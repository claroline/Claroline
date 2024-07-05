import React, {useState} from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {CreationType} from '#/main/app/contexts/workspace/modals/creation/components/type'
import {CreationInfo} from '#/main/app/contexts/workspace/modals/creation/components/info'
import {CreationUpload} from '#/main/app/contexts/workspace/modals/creation/components/upload'

const CreationModal = (props) => {
  const [currentStep, setCurrentStep] = useState('type')

  let StepComponent
  switch (currentStep) {
    case 'type':
      StepComponent = (
        <CreationType
          startCreation={props.startCreation}
          changeStep={setCurrentStep}
        />
      )
      break

    case 'upload':
      StepComponent = (
        <CreationUpload
          changeStep={setCurrentStep}
          create={props.create}
        />
      )
      break

    case 'info':
      StepComponent = (
        <CreationInfo
          create={props.create}
          changeStep={setCurrentStep}
          fadeModal={props.fadeModal}
        />
      )
      break
  }

  return (
    <Modal
      {...omit(props, 'create', 'startCreation')}
      title={trans('new_workspace', {}, 'workspace')}
      subtitle={trans('L\'espace d\'activités est au coeur de votre formation.')}
      centered={true}
      onExited={props.reset}
    >
      {StepComponent}
    </Modal>
  )
}

CreationModal.propTypes = {
  startCreation: T.func.isRequired,
  create: T.func.isRequired,
  reset: T.func.isRequired
}

export {
  CreationModal
}
