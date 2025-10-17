import React, {useState} from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Modal} from '#/main/app/overlays/modal/components/modal'

import {CreationInfo} from '#/main/community/user/modals/creation/components/info'
import {CreationStart} from '#/main/community/user/modals/creation/components/start'

const UserCreationModal = (props) => {
  const [currentStep, setCurrentStep] = useState('start')

  let StepComponent
  switch (currentStep) {
    case 'start':
      StepComponent = (
        <CreationStart
          startCreation={() => {
            props.startCreation()
            setCurrentStep('info')
          }}
          onCreate={props.onCreate}
          fadeModal={props.fadeModal}
        />
      )
      break

    case 'info':
      StepComponent = (
        <CreationInfo
          create={() => props.create().then(response => {
            props.fadeModal()

            if (props.onCreate) {
              props.onCreate(response)
            }

            return response
          })}
          changeStep={setCurrentStep}
        />
      )
      break
  }

  return (
    <Modal
      {...omit(props, 'path', 'contextId', 'startCreation', 'create', 'onCreate', 'reset')}
      title={trans('new_user', {}, 'community')}
      // subtitle={trans('new_user_desc', {}, 'community')}
      centered={true}
      onExited={props.reset}
    >
      {StepComponent}
    </Modal>
  )
}

UserCreationModal.propTypes = {
  path: T.string.isRequired,
  contextId: T.string,
  fadeModal: T.func.isRequired,
  onCreate: T.func,

  // from redux store
  startCreation: T.func.isRequired,
  create: T.func.isRequired,
  reset: T.func.isRequired
}

export {
  UserCreationModal
}
