import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import cloneDeep from 'lodash/cloneDeep'
import isEmpty from 'lodash/isEmpty'
import set from 'lodash/set'

import {trans} from '#/main/core/translation'
import {BaseModal} from '#/main/core/layout/modal/components/base.jsx'
import {Form} from '#/main/core/data/form/components/form.jsx'
import {cleanErrors} from '#/main/core/data/form/utils'

const MODAL_DATA_FORM = 'MODAL_DATA_FORM'

class DataFormModal extends Component {
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
  }

  render() {
    return (
      <BaseModal
        {...this.props}
        className={classes('data-form-modal', this.props.className)}
      >
        <Form
          level={5}
          data={this.state.data}
          errors={this.state.errors}
          pendingChanges={this.state.pendingChanges}
          validating={this.state.validating}
          sections={this.props.sections}
          setErrors={this.setErrors}
          updateProp={this.updateProp}
        />

        <button
          className="modal-btn btn btn-primary"
          disabled={!this.state.pendingChanges || (this.state.validating && !isEmpty(this.state.errors))}
          onClick={this.save}
        >
          {this.props.saveButtonText}
        </button>
      </BaseModal>
    )
  }
}

DataFormModal.propTypes = {
  icon: T.string,
  title: T.string,
  saveButtonText: T.string,
  className: T.string,
  fadeModal: T.func.isRequired,

  // form configuration
  data: T.any,
  sections: T.arrayOf(T.shape({

  })).isRequired,
  save: T.func.isRequired
}

DataFormModal.defaultProps = {
  icon: 'fa fa-fw fa-pencil',
  title: trans('edit'),
  saveButtonText: trans('save'),
  data: {}
}

export {
  MODAL_DATA_FORM,
  DataFormModal
}