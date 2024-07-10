import React, {useState} from 'react'
import {PropTypes as T} from 'prop-types'

import {CreationType} from '#/main/evaluation/skills-framework/modals/creation/components/type'
import {CreationUpload} from '#/main/evaluation/skills-framework/modals/creation/components/upload'
import {CreationForm} from '#/main/evaluation/skills-framework/modals/creation/components/form'
import omit from 'lodash/omit'
import {CreationCopy} from '#/main/evaluation/skills-framework/modals/creation/components/copy'

const CreationModal = (props) => {
  const [currentStep, setCurrentStep] = useState(props.step ||'type')

  switch (currentStep) {
    case 'type':
      return (
        <CreationType
          {...omit(props, 'step')}
          changeStep={setCurrentStep}
        />
      )

    case 'copy':
      return (
        <CreationCopy
          {...omit(props, 'step')}
          changeStep={setCurrentStep}
        />
      )

    case 'upload':
      return (
        <CreationUpload
          {...omit(props, 'step')}
          changeStep={setCurrentStep}
        />
      )

    case 'form':
      return (
        <CreationForm
          {...omit(props, 'step')}
          changeStep={setCurrentStep}
        />
      )
  }
}

CreationModal.propTypes = {
  step: T.string
}

export {
  CreationModal
}
