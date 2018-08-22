import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/core/translation'
import {Modal} from '#/main/app/overlay/modal/components/modal'
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

class ContentCreationModal extends Component {
  constructor(props) {
    super(props)

    this.state = {
      currentStep: 'widget'
    }

    this.changeStep = this.changeStep.bind(this)
  }

  changeStep(step) {
    this.setState({
      currentStep: step
    })
  }

  renderStepTitle() {
    switch(this.state.currentStep) {
      case 'widget':
        return trans('new_widget_select', {}, 'widget')
      case 'dataSource':
        return trans('new_widget_select', {}, 'widget')
      case 'parameters':
        return trans('new_widget_configure', {}, 'widget')
    }
  }

  renderStep() {
    switch(this.state.currentStep) {
      case 'widget':
        return (
          <ContentType
            availableTypes={this.props.availableTypes}
            select={(widget) => {
              this.props.update('type', widget.name)

              if (0 !== widget.sources.length) {
                // we need to configure the data source first
                this.changeStep('dataSource')
              } else {
                this.changeStep('parameters')
              }
            }}
          />
        )
      case 'dataSource':
        return (
          <ContentSource
            sources={this.props.availableSources}
            select={(dataSource) => {
              this.props.update('source', dataSource.name)
              this.changeStep('parameters')
            }}
          />
        )
      case 'parameters':
        return (
          <WidgetContentForm level={5} name={selectors.FORM_NAME} />
        )
    }
  }

  close() {
    this.props.fadeModal()
    this.changeStep('widget')
    this.props.reset()
  }

  render() {
    return (
      <Modal
        {...omit(this.props, 'context', 'add', 'instance', 'saveEnabled', 'availableTypes', 'availableSources', 'fetchContents', 'update', 'reset')}
        icon="fa fa-fw fa-plus"
        title={trans('new_widget', {}, 'widget')}
        subtitle={this.renderStepTitle()}
        onEntering={() => {
          if (0 === this.props.availableTypes.length) {
            this.props.fetchContents(this.props.context)
          }
        }}
        fadeModal={() => this.close()}
      >
        {this.renderStep()}

        {'parameters' === this.state.currentStep &&
          <Button
            className="modal-btn btn"
            type={CALLBACK_BUTTON}
            primary={true}
            disabled={!this.props.saveEnabled}
            label={trans('add', {}, 'actions')}
            callback={() => {
              this.props.add(this.props.instance)
              this.close()
            }}
          />
        }
      </Modal>
    )
  }
}

ContentCreationModal.propTypes = {
  context: T.object.isRequired,
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
  reset: T.func.isRequired
}

export {
  ContentCreationModal
}
