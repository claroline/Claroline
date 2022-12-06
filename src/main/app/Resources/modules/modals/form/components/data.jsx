import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

import cloneDeep from 'lodash/cloneDeep'
import isEmpty from 'lodash/isEmpty'
import omit from 'lodash/omit'
import set from 'lodash/set'

import {trans} from '#/main/app/intl/translation'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {DataFormSection as DataFormSectionTypes} from '#/main/app/content/form/prop-types'
import {FormData} from '#/main/app/content/form/components/data'
import {cleanErrors} from '#/main/app/content/form/utils'
import {CallbackButton} from '#/main/app/buttons'

// todo : maybe use btns from Form definition
// todo : use a redux store

class FormDataModal extends Component {
  constructor(props) {
    super(props)

    this.state = {
      validating: false,
      pendingChanges: false,
      errors: {},
      data: cloneDeep(props.data)
    }

    this.save       = this.save.bind(this)
    this.setErrors  = this.setErrors.bind(this)
    this.updateProp = this.updateProp.bind(this)
  }

  componentDidUpdate(prevProps) {
    if (prevProps.data !== this.props.data) {
      this.setState({
        data: cloneDeep(this.props.data)
      })
    }
  }

  save() {
    this.setState({
      validating: true
    })

    if (isEmpty(this.state.errors)) {
      this.props.save(this.state.data)
      this.props.fadeModal()
    }
  }

  setErrors(errors = {}) {
    this.setState({
      errors: cleanErrors(this.state.errors, errors)
    })
  }

  updateProp(propName, propValue) {
    const newData = cloneDeep(this.state.data)

    set(newData, propName, propValue)

    this.setState({
      validating: false,
      pendingChanges: true,
      data: newData
    })

    if (this.props.onChange) {
      this.props.onChange(newData)
    }
  }

  render() {
    return (
      <Modal
        {...omit(this.props, 'saveButtonText', 'data', 'sections', 'save', 'onChange')}
      >
        <FormData
          level={5}
          data={this.state.data}
          errors={this.state.errors}
          pendingChanges={this.state.pendingChanges}
          validating={this.state.validating}
          definition={this.props.definition || this.props.sections}
          setErrors={this.setErrors}
          updateProp={this.updateProp}
          setMode={() => true}
        >
          {this.props.children}

          <CallbackButton
            htmlType="submit"
            className="modal-btn btn"
            disabled={!this.state.pendingChanges || (this.state.validating && !isEmpty(this.state.errors))}
            callback={this.save}
            primary={true}
          >
            {this.props.saveButtonText}
          </CallbackButton>
        </FormData>
      </Modal>
    )}
}

FormDataModal.propTypes = {
  icon: T.string,
  title: T.string,
  saveButtonText: T.string,
  className: T.string,
  fadeModal: T.func.isRequired,
  children: T.object,

  // form configuration
  data: T.any,
  definition: T.arrayOf(T.shape(
    DataFormSectionTypes.propTypes
  )).isRequired,
  /**
   * @deprecated
   */
  sections: T.arrayOf(T.shape(
    DataFormSectionTypes.propTypes
  )),
  save: T.func.isRequired,
  onChange: T.func
}

FormDataModal.defaultProps = {
  icon: 'fa fa-fw fa-pencil',
  title: trans('edit'),
  saveButtonText: trans('save'),
  data: {}
}

export {
  FormDataModal
}
