import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import cloneDeep from 'lodash/cloneDeep'
import moment from 'moment'

import {trans} from '#/main/app/intl/translation'
import {DataInput} from '#/main/app/data/components/input'
import {ContentHtml} from '#/main/app/content/components/html'
import {ScoreBox} from '#/main/core/layout/evaluation/components/score-box'

import {DropzoneType, CorrectionType, GradeType} from '#/plugin/drop-zone/resources/dropzone/prop-types'
import {computeScoreFromGrades} from '#/plugin/drop-zone/resources/dropzone/utils'
import {validate, isValid} from '#/plugin/drop-zone/resources/dropzone/correction/validator'

const CriteriaForm = props =>
  <div id="criteria-form">
    <ScoreBox
      score={computeScoreFromGrades(props.grades, props.dropzone.parameters.criteriaTotal, props.dropzone.parameters.scoreMax)}
      scoreMax={props.dropzone.parameters.scoreMax}
    />
    {props.dropzone.parameters.criteria.length > 0 ?
      <table className="table">
        <tbody>
          {props.dropzone.parameters.criteria.map(c =>
            <tr key={`criterion-form-${c.id}`}>
              <td>
                <ContentHtml>
                  {c.instruction}
                </ContentHtml>
              </td>
              <td className="criterion-scale-form-row">
                <DataInput
                  id={`criterion-form-${c.id}-radio`}
                  type="choice"
                  label={trans('criterion_form_radio')}
                  options={{
                    inline: true,
                    choices: [...Array(props.dropzone.parameters.criteriaTotal).keys()].reduce((acc, current) => {
                      acc[current] = `${current}`

                      return acc
                    }, {})
                  }}
                  hideLabel={true}
                  value={props.grades.find(g => g.criterion === c.id).value}
                  onChange={value => props.handleUpdate(c.id, parseInt(value))}
                />
              </td>
            </tr>
          )}
        </tbody>
      </table> :
      <div className="alert alert-warning">
        {trans('no_criterion', {}, 'dropzone')}
      </div>
    }
  </div>

CriteriaForm.propTypes = {
  dropzone: T.shape(DropzoneType.propTypes),
  grades: T.arrayOf(T.shape(GradeType.propTypes)),
  handleUpdate: T.func.isRequired
}

export class CorrectionForm extends Component {
  constructor(props) {
    super(props)
    this.state = {
      correction: props.correction,
      pendingChanges: false,
      errors: {}
    }
    this.updateCorrectionCriterion = this.updateCorrectionCriterion.bind(this)
  }

  updateCorrection(property, value) {
    const correction = Object.assign({}, this.state.correction, {[property]: value})
    this.setState({correction: correction, pendingChanges: true})
  }

  updateCorrectionCriterion(criterionId, value) {
    const grades = cloneDeep(this.state.correction.grades)
    const index = grades.findIndex(g => g.criterion === criterionId)

    if (index > -1) {
      const grade = Object.assign({}, grades[index], {value: value})
      grades[index] = grade
    }
    const correction = Object.assign({}, this.state.correction, {grades: grades})

    this.setState({correction: correction, pendingChanges: true})
  }

  validateCorrection() {
    const correction = cloneDeep(this.state.correction)
    correction['lastEditionDate'] = moment().format('YYYY-MM-DDTHH:mm:ss')
    const errors = validate(this.state.correction, this.props.dropzone)
    this.setState({correction: correction, errors: errors}, () => this.saveCorrection())
  }

  saveCorrection() {
    if (isValid(this.state.correction, this.props.dropzone)) {
      this.props.saveCorrection(this.state.correction)
      this.setState({pendingChanges: false})
    }
  }

  render() {
    return (
      <form>
        <div className="card mb-3 correction-form-panel">
          {this.props.dropzone.display.correctionInstruction &&
            <div id="correction-instruction-container">
              <h2>{trans('correction_instruction', {}, 'dropzone')}</h2>
              <ContentHtml>
                {this.props.dropzone.display.correctionInstruction}
              </ContentHtml>
              <hr/>
            </div>
          }
          {this.props.dropzone.parameters.criteriaEnabled ?
            <CriteriaForm
              dropzone={this.props.dropzone}
              grades={this.state.correction.grades}
              handleUpdate={this.updateCorrectionCriterion}
            /> :
            <DataInput
              id="score"
              type="number"
              label={trans('score', {}, 'platform')}
              value={this.state.correction.score !== null ? this.state.correction.score : undefined}
              onChange={value => this.updateCorrection('score', parseInt(value))}
              error={get(this.state.errors, 'score')}
            />
          }
          {this.props.dropzone.parameters.commentInCorrectionEnabled &&
            <DataInput
              id="comment"
              type="html"
              label={trans('comment', {}, 'platform')}
              value={this.state.correction.comment || ''}
              onChange={value => this.updateCorrection('comment', value)}
              options={{minRows: 3}}
              error={get(this.state.errors, 'comment')}
            />
          }
          <div className="btn-group btn-group-right">
            <button
              className="btn btn-outline-secondary"
              type="button"
              onClick={() => this.props.cancelCorrection(this.props.navigate)}
            >
              {trans('cancel', {}, 'platform')}
            </button>
            {this.props.showSubmitButton && this.props.correction.startDate !== this.props.correction.lastEditionDate &&
              <button
                className="btn btn-outline-secondary"
                type="button"
                disabled={this.state.pendingChanges ||
                (this.props.dropzone.parameters.commentInCorrectionEnabled &&
                  this.props.dropzone.parameters.commentInCorrectionForced &&
                  !this.state.correction.comment)
                }
                onClick={() => this.props.submitCorrection(this.props.correction.id, this.props.navigate)}
              >
                {trans('submit_correction', {}, 'dropzone')}
              </button>
            }
            <button
              className="btn btn-primary"
              type="button"
              disabled={this.props.dropzone.parameters.commentInCorrectionEnabled &&
                this.props.dropzone.parameters.commentInCorrectionForced &&
                !this.state.correction.comment
              }
              onClick={() => this.validateCorrection()}
            >
              {trans('save', {}, 'platform')}
            </button>
          </div>
        </div>
      </form>
    )
  }
}

CorrectionForm.propTypes = {
  dropzone: T.shape(DropzoneType.propTypes),
  correction: T.shape(CorrectionType.propTypes),
  showSubmitButton: T.bool.isRequired,
  saveCorrection: T.func.isRequired,
  submitCorrection: T.func.isRequired,
  cancelCorrection: T.func.isRequired,
  navigate: T.func.isRequired
}

CorrectionForm.defaultProps = {
  showSubmitButton: false,
  submitCorrection: () => {}
}