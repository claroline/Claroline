import React, {Component, PropTypes as T} from 'react'
import {t, tex} from './../../utils/translate'
import {ContentEditable, Textarea} from './../../components/form/textarea.jsx'
import {FormGroup} from './../../components/form/form-group.jsx'
import {actions} from './editor'
import {TooltipButton} from './../../components/form/tooltip-button.jsx'
import Popover from 'react-bootstrap/lib/Popover'
import {ErrorBlock} from './../../components/form/error-block.jsx'
import get from 'lodash/get'
import classes from 'classnames'

class ChoiceItem extends Component {
  constructor(props) {
    super(props)
    this.state = {showFeedback: false}
  }

  render() {
    return (
      <div className={classes(
          'choice-item-cloze',
          {'positive-score': this.props.answer.score > 0},
          {'negative-score': this.props.answer.score <= 0}
        )
      }>
        <div className='row'>
          <div className='hole-form-row'>
            <div className="col-xs-4">
              <ContentEditable
                id={`item-${this.props.id}-answer`}
                className="form-control input-sm"
                type="text"
                content={this.props.answer.text}
                onChange={text => this.props.onChange(
                  actions.updateAnswer(
                    this.props.hole.id,
                    'text',
                    this.props.answer.text,
                    this.props.answer.caseSensitive,
                    text
                  )
                )}
              />
            </div>
            <div className="col-xs-1">
              <input
                 type="checkbox"
                 checked={this.props.answer.caseSensitive}
                 onChange={e => this.props.onChange(
                   actions.updateAnswer(
                     this.props.hole.id,
                     'caseSensitive',
                     this.props.answer.text,
                     this.props.answer.caseSensitive,
                     e.target.checked
                   )
                 )}
               />
          </div>
          <div className="col-xs-4">
            <input
              className="form-control choice-form"
              type="number"
              value={this.props.answer.score}
              onChange={e => this.props.onChange(
                actions.updateAnswer(
                  this.props.hole.id,
                  'score',
                  this.props.answer.text,
                  this.props.answer.caseSensitive,
                  parseInt(e.target.value)
                )
              )}
            />
          </div>
          <div className="col-xs-3">
            <TooltipButton
              id={`choice-${this.props.id}-feedback-toggle`}
              className="fa fa-comments-o"
              title={tex('choice_feedback_info')}
              onClick={() => this.setState({showFeedback: !this.state.showFeedback})}
            />
            <TooltipButton
              id={`answer-${this.props.id}-delete`}
              className="fa fa-trash-o"
              title={t('delete')}
              onClick={() => this.props.onChange(
                actions.removeAnswer(this.props.answer.text, this.props.answer.caseSensitive)
              )}
            />
          </div>
        </div>
        <div className="col-xs-12">
          {get(this.props, `_errors.answers.answer.${this.props.id}.text`) &&
            <ErrorBlock text={this.props._errors.answers.answer[this.props.id].text} warnOnly={!this.props.validating}/>
          }
          {get(this.props, `_errors.answers.answer.${this.props.id}.score`) &&
            <ErrorBlock text={this.props._errors.answers.answer[this.props.id].score} warnOnly={!this.props.validating}/>
          }
        </div>
      </div>
      {this.state.showFeedback &&
        <div className="feedback-container hole-form-row">
          <Textarea
            id={`choice-${this.props.id}-feedback`}
            title={tex('feedback')}
            content={this.props.answer.feedback}
            onChange={text => this.props.onChange(
              actions.updateAnswer(
                this.props.hole.id,
                'feedback',
                this.props.answer.text,
                this.props.answer.caseSensitive,
                text
              )
            )}
          />
        </div>
        }
      </div>
    )
  }
}

ChoiceItem.defaultProps = {
  answer: {
    feedback: ''
  }
}

ChoiceItem.propTypes = {
  answer: T.shape({
    score: T.number,
    feedback: T.string,
    text: T.string,
    caseSensitive: T.bool
  }).isRequired,
  hole: T.shape({
    id: T.string.isRequired
  }).isRequired,
  id: T.number.isRequired,
  deletable: T.bool.isRequired,
  onChange: T.func.isRequired,
  validating: T.bool.isRequired,
  _errors: T.object
}

class HoleForm extends Component {
  constructor(props) {
    super(props)
    this.state = {showFeedback: false}

    this.offsetTop = window.scrollY + window.innerHeight / 2 - (420/2)
    this.offsetLeft = window.scrollX + window.innerWidth / 2 - (420/2)

    //this.hole = this.props.item.holes.find(hole => hole.id === this.props.item._holeId)
  }

  getHoleAnswers(hole) {
    //http://stackoverflow.com/questions/10865025/merge-flatten-an-array-of-arrays-in-javascript
    //concat is here to flatten the array
    return [].concat.apply(
      [],
      this.props.item.solutions.filter(solution => solution.holeId === hole.id).map(solution => solution.answers)
    )
  }

  getHole() {
    return this.props.item.holes.find(hole => hole.id === this.props.item._holeId)
  }

  closePopover() {
    this.props.onChange(actions.closePopover())
  }

  removeAndClose() {
    this.props.onChange(actions.removeHole(this.getHole().id))
    this.closePopover()
  }

  render() {
    return (
      <Popover
        bsClass="hole-form-content"
        id={this.getHole().id}
        placement="right"
        positionLeft={this.offsetLeft}
        positionTop={this.offsetTop}
      >
        <div className="panel-default">
          <div className="panel-body pull-right close-popover hole-form-row">
            <i onClick={this.removeAndClose.bind(this)} className="fa fa-trash-o"></i>
            {'\u00a0'}
            {!this.props._errors.answers &&
              <b onClick={this.closePopover.bind(this)}>x</b>
            }
          </div>
          <div className="panel-body">
            <div className="row">
              <div className="hole-form-row">
                <div className="col-xs-2">
                  {tex('size')}
                </div>
                <input
                  id={`item-${this.getHole().id}-size`}
                  type="number"
                  min="0"
                  value={this.getHole().size}
                  className="col-xs-2 form-control hole-size"
                  onChange={e => this.props.onChange(
                    actions.updateHole(this.getHole().id, 'size', parseInt(e.target.value))
                  )}
                />
                <div className="col-xs-1">
                  <input
                    type="checkbox"
                    checked={this.getHole()._multiple}
                    onChange={e => this.props.onChange(
                      actions.updateHole(
                        this.getHole().id,
                        '_multiple',
                        e.target.checked
                      )
                    )}
                  />
                </div>
                <div className="col-xs-6">
                  {tex('submit_a_list')}
                </div>
              </div>
            </div>
            <div>
              {get(this.props, '_errors.answers.size') &&
                <ErrorBlock text={this.props._errors.answers.size} warnOnly={!this.props.validating}/>
              }
              {get(this.props, '_errors.answers.multiple') &&
                <ErrorBlock text={this.props._errors.answers.multiple} warnOnly={!this.props.validating}/>
              }
              {get(this.props, '_errors.answers.duplicate') &&
                <ErrorBlock text={this.props._errors.answers.duplicate} warnOnly={!this.props.validating}/>
              }
              {get(this.props, '_errors.answers.value') &&
                <ErrorBlock text={this.props._errors.answers.value} warnOnly={!this.props.validating}/>
              }
            </div>
            <div className="hole-form-row">
              <div className="col-xs-5"><b>{tex('key_word')}</b></div>
              <div className="col-xs-7"><b>{tex('score')}</b></div>
            </div>
            {this.props.item.solutions.find(solution => solution.holeId === this.getHole().id).answers.map((answer, index) => {
              return (<ChoiceItem
                key={index}
                id={index}
                score={answer.score}
                feedback={answer.feedback}
                deletable={index > 0}
                onChange={this.props.onChange}
                hole={this.getHole()}
                answer={answer}
                validating={this.props.validating}
                _errors={this.props._errors}
              />)
            })}

            {this.state.showFeedback &&
              <div className="feedback-container hole-form-row">
                <Textarea
                  id={`choice-${this.getHole().id}-feedback`}
                  title={tex('feedback')}
                  onChange={text => this.props.onChange(
                    actions.updateAnswer(this.getHole().id, 'feedback', text)
                  )}
                />
              </div>
            }
            <div className="hole-form-row">
              <button
                className="btn btn-default"
                onClick={() => this.props.onChange(
                  actions.addAnswer(this.getHole().id))}
                type="button"
              >
                <i className="fa fa-plus"/>
                {tex('key_word')}
              </button>
            </div>
          </div>
        </div>
      </Popover>
    )
  }
}

HoleForm.propTypes = {
  item: T.shape({
    _holeId: T.string.isRequired,
    holes: T.array.isRequired,
    solutions: T.array.isRequired
  }),
  onChange: T.func.isRequired,
  validating: T.bool.isRequired,
  _errors: T.object
}

export class Cloze extends Component {
  constructor(props) {
    super(props)
    this.selection = null
    this.word = null
    this.fnTextUpdate = () => {}
    this.state = { allowCloze: true }
    this.changeEditorMode = this.changeEditorMode.bind(this)
  }

  onSelect(word, cb) {
    this.word = word
    this.fnTextUpdate = cb
  }

  onHoleClick(el) {
    if (el.classList.contains('edit-hole-btn')) {
      this.props.onChange(actions.openHole(el.dataset.holeId))
    } else {
      if (el.classList.contains('delete-hole-btn')) {
        this.props.onChange(actions.removeHole(el.dataset.holeId))
      }
    }
  }

  changeEditorMode(editorState) {
    this.setState({ allowCloze: editorState.minimal})
  }

  addHole() {
    return actions.addHole(this.word, this.fnTextUpdate.bind(this), this.selection)
  }

  render() {
    return(
      <div>
        <FormGroup
          controlId="cloze-text"
          label={t('text')}
          warnOnly={!this.props.validating}
          error={get(this.props.item, '_errors.text')}
        >
          <Textarea
            id='cloze-item-text'
            onChange={(value) => this.props.onChange(actions.updateText(value))}
            onSelect={this.onSelect.bind(this)}
            onClick={this.onHoleClick.bind(this)}
            content={this.props.item._text}
            onChangeMode={this.changeEditorMode}
          />
        </FormGroup>
        <button
          type="button"
          className="btn btn-default"
          disabled={!this.state.allowCloze}
          onClick={() => this.props.onChange(this.addHole())}><i className="fa fa-plus"/>
          {tex('create_cloze')}
        </button>
        {(this.props.item._popover && this.props.item._holeId) &&
          <div>
            <HoleForm
              item={this.props.item}
              onChange={this.props.onChange}
              validating={this.props.validating}
              _errors={this.props.item._errors}
            />
          </div>
        }
      </div>
    )
  }
}

Cloze.propTypes = {
  item: T.shape({
    id: T.string.isRequired,
    text: T.string.isRequired,
    _text: T.string.isRequired,
    _errors: T.object,
    _popover: T.bool,
    _holeId: T.string
  }),
  onChange: T.func.isRequired,
  validating: T.bool.isRequired
}
