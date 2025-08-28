import React, {useState} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

import {
  DataSource as DataSourceTypes,
  Widget as WidgetTypes
} from '#/main/core/widget/prop-types'

import {ContentType} from '#/main/core/widget/content/modals/creation/components/type'
import {WidgetContentForm} from '#/main/core/widget/content/components/form'
import {selectors} from '#/main/core/widget/content/modals/creation/store'

const ContentCreationModal = props => {
  const [currentStep, changeStep] = useState('type')

  const renderStepTitle = () => {
    switch (currentStep) {
      case 'type':
        return trans('new_widget_select', {}, 'widget')
      case 'parameters':
        return trans('new_widget_configure', {}, 'widget')
    }
  }

  const renderStep = () => {
    switch (currentStep) {
      case 'type':
        return (
          <ContentType
            types={props.availableTypes}
            sources={props.availableSources}
            select={(widget, source = null) => {
              props.update('type', widget)
              props.update('source', source)

              changeStep('parameters')
            }}
          />
        )
      case 'parameters':
        return (
          <WidgetContentForm
            level={5}
            name={selectors.FORM_NAME}
            currentContext={props.currentContext}
            isNew={true}
            onSave={(formData) => {
              props.add(formData)
              props.fadeModal()
            }}
          >
            <Button
              type={CALLBACK_BUTTON}
              label={trans('back')}
              className="btn btn-text-body me-auto"
              callback={() => changeStep('type')}
            />
          </WidgetContentForm>
        )
    }
  }

  return (
    <Modal
      {...omit(props, 'currentContext', 'add', 'availableTypes', 'availableSources', 'fetchContents', 'update', 'reset')}
      title={trans('new_widget', {}, 'widget')}
      subtitle={renderStepTitle()}
      onEntering={() => {
        if (0 === props.availableTypes.length) {
          props.fetchContents(props.currentContext.type, get(props.currentContext, 'data.id'))
        }
      }}
      onExited={props.reset}
    >
      {renderStep()}
    </Modal>
  )
}

ContentCreationModal.propTypes = {
  currentContext: T.object.isRequired,
  fadeModal: T.func.isRequired,
  add: T.func.isRequired,

  // from redux store
  availableTypes: T.arrayOf(T.shape(
    WidgetTypes.propTypes
  )).isRequired,
  availableSources: T.arrayOf(T.shape(
    DataSourceTypes.propTypes
  )).isRequired,
  fetchContents: T.func.isRequired,
  update: T.func.isRequired,
  reset: T.func.isRequired
}

export {
  ContentCreationModal
}
