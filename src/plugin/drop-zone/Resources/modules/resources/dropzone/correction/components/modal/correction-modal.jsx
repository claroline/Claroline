import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

import {registry} from '#/main/app/modals/registry'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {trans} from '#/main/app/intl/translation'
import {ContentHtml} from '#/main/app/content/components/html'
import {ScoreBox} from '#/main/core/layout/evaluation/components/score-box'

import {DropzoneType} from '#/plugin/drop-zone/resources/dropzone/prop-types'
import {validateNotBlank} from '#/plugin/drop-zone/resources/dropzone/correction/validator'
import {DataInput} from '#/main/app/data/components/input'

const MODAL_CORRECTION = 'MODAL_CORRECTION'

class DenialBox extends Component {
  constructor(props) {
    super(props)
    this.state = {
      correction: props.correction,
      error: null,
      showForm: false
    }
  }

  updateCorrectionDeniedComment(value) {
    const correction = Object.assign({}, this.state.correction, {correctionDeniedComment: value})
    this.setState({correction: correction})
  }

  cancelDeniedComment() {
    this.setState({correction: this.props.correction, error: null, showForm: false})
  }

  saveDeniedComment() {
    if (!this.state.error) {
      const correction = Object.assign({}, this.state.correction, {correctionDenied: true})
      this.setState(
        {correction: correction, error: null, showForm: false},
        () => this.props.denyCorrection(this.state.correction.id, this.state.correction.correctionDeniedComment)
      )
    }
  }

  validateDeniedComment() {
    const error = validateNotBlank(this.state.correction.correctionDeniedComment)
    this.setState(
      {error: error},
      () => this.saveDeniedComment()
    )
  }

  render() {
    return (
      <div id="denial-box">
        {this.state.correction.correctionDenied &&
          <ContentHtml>
            {this.state.correction.correctionDeniedComment}
          </ContentHtml>
        }
        {!this.state.correction.correctionDenied && !this.state.showForm &&
          <button
            className="btn btn-danger"
            type="button"
            onClick={() => this.setState({showForm: true})}
          >
            {trans('deny_correction', {}, 'dropzone')}
          </button>
        }
        {!this.state.correction.correctionDenied && this.state.showForm &&
          <div>
            <DataInput
              id="correction-denied-comment"
              type="html"
              label={trans('denial_reason', {}, 'dropzone')}
              value={this.state.correction.correctionDeniedComment || ''}
              onChange={value => this.updateCorrectionDeniedComment(value)}
              options={{minRows: 3}}
              error={this.state.error}
            />
            <div className="btn-group btn-group-right">
              <button
                className="btn btn-default"
                type="button"
                onClick={() => this.cancelDeniedComment()}
              >
                {trans('cancel', {}, 'platform')}
              </button>
              <button
                className="btn btn-primary"
                type="button"
                disabled={!this.state.correction.correctionDeniedComment}
                onClick={() => this.validateDeniedComment()}
              >
                {trans('save', {}, 'platform')}
              </button>
            </div>
          </div>
        }
      </div>
    )
  }
}

DenialBox.propTypes = {
  correction: T.object.isRequired,
  denyCorrection: T.func.isRequired,
  fadeModal: T.func.isRequired
}

class CorrectionModal extends Component {
  constructor(props) {
    super(props)
    this.state = {
      correction: props.correction
    }
  }

  render() {
    return (
      <Modal {...this.props}>
        <div className="modal-body">
          {this.props.dropzone.display.showScore &&
            <ScoreBox
              score={this.props.correction.score}
              scoreMax={this.props.dropzone.parameters.scoreMax}
              size="lg"
            />
          }
          {this.props.dropzone.parameters.criteriaEnabled && this.props.dropzone.parameters.criteria.length > 0 &&
            <table className="table">
              <tbody>
                {this.props.dropzone.parameters.criteria.map(c =>
                  <tr key={`correction-criterion-${c.id}`}>
                    <td>
                      <ContentHtml>
                        {c.instruction}
                      </ContentHtml>
                    </td>
                    <td className="criterion-scale-form-row">
                      <DataInput
                        id={`criterion-form-${c.id}-radio`}
                        label="correction_criterion_radio"
                        type="choice"
                        options={{
                          inline: true,
                          choices: [...Array(this.props.dropzone.parameters.criteriaTotal).keys()].reduce((acc, current) => {
                            acc[current] = `${current}`

                            return acc
                          }, {})
                        }}
                        disabled={true}
                        hideLabel={true}
                        value={this.props.correction.grades.find(g => g.criterion === c.id) ?
                          this.props.correction.grades.find(g => g.criterion === c.id).value :
                          ''
                        }
                        onChange={() => {}}
                      />
                    </td>
                  </tr>
                )}
              </tbody>
            </table>
          }
          {this.state.correction.comment &&
            <div>
              <h3>{trans('comment', {}, 'platform')}</h3>
              <ContentHtml className="correction-comment">
                {this.state.correction.comment}
              </ContentHtml>
            </div>
          }
          {this.props.showDenialBox &&
            <DenialBox {...this.props}/>
          }
        </div>
        <div className="modal-footer">
          <button
            className="btn btn-default"
            type="button"
            onClick={this.props.fadeModal}
          >
            {trans('close', {}, 'platform')}
          </button>
        </div>
      </Modal>
    )
  }
}

CorrectionModal.propTypes = {
  correction: T.object.isRequired,
  dropzone: T.shape(DropzoneType.propTypes).isRequired,
  showDenialBox: T.bool.isRequired,
  denyCorrection: T.func.isRequired,
  fadeModal: T.func.isRequired
}

CorrectionModal.defaultProps = {
  showDenialBox: false,
  denyCorrection: () => {}
}

registry.add(MODAL_CORRECTION, CorrectionModal)

export {
  MODAL_CORRECTION,
  CorrectionModal
}