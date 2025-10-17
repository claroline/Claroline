import React, {useState} from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {CreationType} from '#/main/core/workspace/modals/creation/components/type'
import {CreationInfo} from '#/main/core/workspace/modals/creation/components/info'
import {CreationUpload} from '#/main/core/workspace/modals/creation/components/upload'

const CreationModal = (props) => {
  const [currentStep, setCurrentStep] = useState('type')

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

    case 'upload':
      StepComponent = (
        <CreationUpload
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
      {...omit(props, 'model', 'create', 'onCreate', 'startCreation', 'reset')}
      title={trans('new_workspace', {}, 'workspace')}
      subtitle={trans('new_workspace_desc', {}, 'workspace')}
      centered={true}
      onExited={props.reset}
    >
      {StepComponent}
    </Modal>
  )
}

CreationModal.propTypes = {
  model: T.bool,
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
