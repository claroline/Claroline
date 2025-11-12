import React, {useState} from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'
import merge from 'lodash/merge'

import {CreationType} from '#/main/evaluation/sequence/modals/creation/components/type'
import {CreationUpload} from '#/main/evaluation/sequence/modals/creation/components/upload'
import {CreationForm} from '#/main/evaluation/sequence/modals/creation/components/form'
import {CreationCopy} from '#/main/evaluation/sequence/modals/creation/components/copy'

const CreationModal = (props) => {
  const [currentStep, setCurrentStep] = useState('type')

  const create = () => props.create().then(response => {
    props.fadeModal()

    if (props.onCreate) {
      props.onCreate(response)
    }

    return response
  })

  switch (currentStep) {
    case 'type':
      return (
        <CreationType
          {...omit(props, 'workspace', 'create', 'onCreate', 'startCreation')}
          changeStep={setCurrentStep}
          startCreation={(formData) => props.startCreation(merge({}, formData, {
            workspace: props.workspace
          }))}
        />
      )

    case 'copy':
      return (
        <CreationCopy
          {...omit(props, 'workspace', 'create', 'onCreate', 'startCreation')}
          changeStep={setCurrentStep}
        />
      )

    case 'upload':
      return (
        <CreationUpload
          {...omit(props, 'workspace', 'create', 'onCreate', 'startCreation')}
          changeStep={setCurrentStep}
        />
      )

    case 'form':
      return (
        <CreationForm
          {...omit(props, 'workspace', 'create', 'onCreate', 'startCreation')}
          changeStep={setCurrentStep}
          create={create}
        />
      )
  }
}

CreationModal.propTypes = {
  workspace: T.object.isRequired,
  onCreate: T.func,

  // from modal
  fadeModal: T.func.isRequired,
  // from store
  create: T.func.isRequired,
  startCreation: T.func.isRequired
}

export {
  CreationModal
}
