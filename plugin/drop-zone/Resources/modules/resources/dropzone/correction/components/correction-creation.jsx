import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/core/translation'

import {DropzoneType, DropType} from '#/plugin/drop-zone/resources/dropzone/prop-types'
import {generateCorrection} from '#/plugin/drop-zone/resources/dropzone/utils'
import {CorrectionForm} from '#/plugin/drop-zone/resources/dropzone/correction/components/correction-form.jsx'

export class CorrectionCreation extends Component {
  constructor(props) {
    super(props)
    this.state = {
      correction: {},
      showCorrectionForm: false
    }
    this.saveCorrection = this.saveCorrection.bind(this)
    this.cancelCorrection = this.cancelCorrection.bind(this)
  }

  showCorrectionCreationForm() {
    const correction = generateCorrection(this.props.drop.id, this.props.currentUser, this.props.dropzone)
    this.setState({correction: correction, showCorrectionForm: true})
  }

  saveCorrection(correction) {
    this.props.saveCorrection(correction)
    this.cancelCorrection()
  }

  cancelCorrection() {
    this.setState({correction: {}, showCorrectionForm: false})
  }

  render() {
    return (this.state.showCorrectionForm ?
      <CorrectionForm
        correction={this.state.correction}
        dropzone={this.props.dropzone}
        saveCorrection={this.saveCorrection}
        cancelCorrection={this.cancelCorrection}
      /> :
      <button
        className="btn btn-primary pull-right"
        type="button"
        onClick={() => this.showCorrectionCreationForm()}
      >
        {trans('add_correction', {}, 'dropzone')}
      </button>
    )
  }
}

CorrectionCreation.propTypes = {
  currentUser: T.object,
  dropzone: T.shape(DropzoneType.propTypes),
  drop: T.shape(DropType.propTypes),
  saveCorrection: T.func.isRequired
}