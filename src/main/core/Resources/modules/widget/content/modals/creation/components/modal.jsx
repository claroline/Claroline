import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

import {
  DataSource as DataSourceTypes,
  Widget as WidgetTypes
} from '#/main/core/widget/prop-types'

import {ContentSource} from '#/main/core/widget/content/modals/creation/components/source'
import {ContentType} from '#/main/core/widget/content/modals/creation/components/type'
import {WidgetContentForm} from '#/main/core/widget/content/components/form'
import {WidgetInstance as WidgetInstanceTypes} from '#/main/core/widget/content/prop-types'
import {selectors} from '#/main/core/widget/content/modals/creation/store'

const ContentCreationModal = props => {
  const renderStepTitle = () => {
    switch(props.currentStep) {
      case 'widget':
      case 'dataSource':
        return trans('new_widget_select', {}, 'widget')
      case 'parameters':
        return trans('new_widget_configure', {}, 'widget')
    }
  }

  const renderStep = () => {
    const currentType = props.availableTypes.find(type => type.name === props.instance.type)

    switch (props.currentStep) {
      case 'widget':
        return (
          <ContentType
            types={props.availableTypes}
            select={(widget) => {
              props.update('type', widget.name)

              if (0 !== widget.sources.length) {
                // we need to configure the data source first
                props.changeStep('dataSource')
              } else {
                props.changeStep('parameters')
              }
            }}
          />
        )
      case 'dataSource':
        return (
          <ContentSource
            sources={props.availableSources.filter(source => -1 !== currentType.sources.indexOf(source.type))}
            select={(dataSource) => {
              props.update('source', dataSource.name)
              props.changeStep('parameters')
            }}
          />
        )
      case 'parameters':
        return (
          <WidgetContentForm level={5} name={selectors.FORM_NAME} currentContext={props.currentContext} />
        )
    }
  }

  const close = () => {
    props.fadeModal()
    props.changeStep('widget')
    props.reset()
  }

  return (
    <Modal
      {...omit(props, 'currentContext', 'add', 'instance', 'saveEnabled', 'availableTypes', 'availableSources', 'fetchContents', 'update', 'reset', 'currentStep', 'changeStep')}
      icon="fa fa-fw fa-plus"
      title={trans('new_widget', {}, 'widget')}
      subtitle={renderStepTitle()}
      onEntering={() => {
        if (0 === props.availableTypes.length) {
          props.fetchContents(props.currentContext)
        }
      }}
      fadeModal={close}
      size="lg"
    >
      {renderStep()}

      {'parameters' === props.currentStep &&
        <Button
          className="modal-btn"
          variant="btn"
          size="lg"
          type={CALLBACK_BUTTON}
          primary={true}
          disabled={!props.saveEnabled}
          label={trans('add', {}, 'actions')}
          callback={() => {
            props.add(props.instance)
            close()
          }}
        />
      }
    </Modal>
  )
}

ContentCreationModal.propTypes = {
  currentContext: T.object.isRequired,
  fadeModal: T.func.isRequired,
  add: T.func.isRequired,

  // from redux store
  instance: T.shape(
    WidgetInstanceTypes.propTypes
  ).isRequired,
  saveEnabled: T.bool.isRequired,
  availableTypes: T.arrayOf(T.shape(
    WidgetTypes.propTypes
  )).isRequired,
  availableSources: T.arrayOf(T.shape(
    DataSourceTypes.propTypes
  )).isRequired,
  fetchContents: T.func.isRequired,
  update: T.func.isRequired,
  reset: T.func.isRequired,
  currentStep: T.string.isRequired,
  changeStep: T.func.isRequired
}

export {
  ContentCreationModal
}
