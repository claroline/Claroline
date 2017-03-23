import React, {Component, PropTypes as T} from 'react'
import classes from 'classnames'
import get from 'lodash/get'
import Popover from 'react-bootstrap/lib/Popover'

import {t, tex} from './../../utils/translate'
import {ContentEditable, Textarea} from './../../components/form/textarea.jsx'
import {FormGroup} from './../../components/form/form-group.jsx'
import {CheckGroup} from './../../components/form/check-group.jsx'
import {actions} from './editor'
import {TooltipButton} from './../../components/form/tooltip-button.jsx'
import {ErrorBlock} from './../../components/form/error-block.jsx'

class ChoiceItem extends Component {
  constructor(props) {
    super(props)
    this.state = {showFeedback: false}
  }

  render() {
    return (
      <div className={classes(
          'answer-item keyword-item',
          {'expected-answer': this.props.answer.score > 0},
          {'unexpected-answer': this.props.answer.score <= 0}
        )
      }>
        <div className="text-fields">
          <input
            type="text"
            id={`keyword-${this.props.id}-answer`}
            title={tex('response')}
            value={this.props.answer.text}
            className="form-control"
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

          {this.state.showFeedback &&
            <div className="feedback-container">
              <Textarea
                id={`keyword-${this.props.id}-feedback`}
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

        <div className="keyword-case-sensitive">
          <input
            type="checkbox"
            title={tex('words_case_sensitive')}
            checked={this.props.caseSensitive}
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

        <div className="right-controls">
          <input
            title={tex('score')}
            className="form-control keyword-score"
            type="number"
            value={this.props.answer.score}
            onChange={e => this.props.onChange(
              actions.updateAnswer(
                this.props.hole.id,
                'score',
                this.props.answer.text,
                this.props.answer.caseSensitive,
                Number(e.target.value)
              )
            )}
          />

          <TooltipButton
            id={`keyword-${this.props.id}-feedback-toggle`}
            className="btn-link-default"
            label={<span className="fa fa-fw fa-comments-o"/>}
            title={tex('choice_feedback_info')}
            onClick={() => this.setState({showFeedback: !this.state.showFeedback})}
          />
          <TooltipButton
            id={`keyword-${this.props.id}-delete`}
            className="btn-link-default"
            enabled={this.props.deletable}
            title={t('delete')}
            label={<span className="fa fa-fw fa-trash-o"/>}
            onClick={() => this.props.onChange(
              actions.removeAnswer(this.props.answer.text, this.props.answer.caseSensitive)
            )}
          />
        </div>
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

    /*this.offsetTop = window.scrollY + window.innerHeight / 2 - (420/2)
    this.offsetLeft = window.scrollX + window.innerWidth / 2 - (420/2)*/

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
        id={this.getHole().id}
        placement="bottom"
        positionLeft={this.props.positionLeft}
        positionTop={this.props.positionTop}
        title={
          <div>
            {tex('cloze_edit_hole')}

            <div className="popover-actions">
              <TooltipButton
                id={`hole-${this.getHole().id}-delete`}
                title={tex('delete')}
                className="btn-link-default"
                label={<span className="fa fa-fw fa-trash-o" />}
                onClick={this.removeAndClose.bind(this)}
              />
              <TooltipButton
                id={`hole-${this.getHole().id}-close`}
                title={tex('close')}
                className="btn-link-default"
                label={<span className="fa fa-fw fa-times" />}
                onClick={this.closePopover.bind(this)}
              />
            </div>
          </div>
        }
      >
        <FormGroup
          controlId={`item-${this.getHole().id}-size`}
          label={tex('size')}
        >
          <input
            id={`item-${this.getHole().id}-size`}
            type="number"
            min="0"
            value={this.getHole().size}
            className="form-control"
            onChange={e => this.props.onChange(
              actions.updateHole(this.getHole().id, 'size', parseInt(e.target.value))
            )}
          />
        </FormGroup>

        {get(this.props, '_errors.answers.size') &&
        <ErrorBlock text={this.props._errors.answers.size} warnOnly={!this.props.validating}/>
        }

        <CheckGroup
          checkId={`item-${this.getHole().id}-list`}
          label={tex('submit_a_list')}
          checked={this.getHole()._multiple}
          onChange={e => this.props.onChange(
            actions.updateHole(
              this.getHole().id,
              '_multiple',
              e.target.checked
            )
          )}
        />

        <div className="keyword-items">
          {get(this.props, '_errors.answers.multiple') &&
            <ErrorBlock text={this.props._errors.answers.multiple} warnOnly={!this.props.validating}/>
          }
          {get(this.props, '_errors.answers.duplicate') &&
            <ErrorBlock text={this.props._errors.answers.duplicate} warnOnly={!this.props.validating}/>
          }
          {get(this.props, '_errors.answers.value') &&
            <ErrorBlock text={this.props._errors.answers.value} warnOnly={!this.props.validating}/>
          }
          {get(this.props, `_errors.answers.text`) &&
            <ErrorBlock text={this.props._errors.answers.text} warnOnly={!this.props.validating}/>
          }
          {get(this.props, `_errors.answers.score`) &&
            <ErrorBlock text={this.props._errors.answers.score} warnOnly={!this.props.validating}/>
          }

          <ul>
            {this.props.item.solutions.find(solution => solution.holeId === this.getHole().id).answers.map((answer, index) =>
              <ChoiceItem
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
              />
            )}
          </ul>

          <div className="footer">
            <button
              className="btn btn-default"
              onClick={() => this.props.onChange(
                actions.addAnswer(this.getHole().id))}
              type="button"
            >
              <span className="fa fa-fw fa-plus" />
              {tex('words_add_word')}
            </button>
          </div>
        </div>
      </Popover>
    )
  }
}

HoleForm.propTypes = {
  positionLeft: T.number.isRequired,
  positionTop: T.number.isRequired,
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
    if (el.classList.contains('edit-hole-btn') || el.classList.contains('edit-hole-btn-icon')) {
      let btnElement
      if (el.classList.contains('edit-hole-btn-icon')) {
        btnElement = el.parentElement
      } else {
        btnElement = el
      }

      // Let's calculate the popover position
      let left = btnElement.offsetLeft
      let top  = btnElement.offsetTop

      left += btnElement.offsetWidth / 2 // center popover and edit btn
      top  += btnElement.offsetHeight // position popover below edit btn

      left -= 180 // half size of the popover
      top  += 25 // take into account the form group label

      this.props.onChange(actions.openHole(el.dataset.holeId, left, top))
    } else if (el.classList.contains('delete-hole-btn') || el.classList.contains('delete-hole-btn-icon')) {
      this.props.onChange(actions.removeHole(el.dataset.holeId))
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
      <fieldset className="cloze-editor">
        <FormGroup
          controlId="cloze-text"
          label={t('text')}
          warnOnly={!this.props.validating}
          error={get(this.props.item, '_errors.text')}
        >
          <Textarea
            id="cloze-text"
            className="cloze-text"
            onChange={(value) => this.props.onChange(actions.updateText(value))}
            onSelect={this.onSelect.bind(this)}
            onClick={this.onHoleClick.bind(this)}
            content={this.props.item._text}
            onChangeMode={this.changeEditorMode}
          />
        </FormGroup>

        <div className="footer">
          <button
            type="button"
            className="btn btn-default"
            disabled={!this.state.allowCloze}
            onClick={() => this.props.onChange(this.addHole())}
          >
            <span className="fa fa-fw fa-plus" />
            {tex('create_cloze')}
          </button>
        </div>

        {(this.props.item._popover && this.props.item._holeId) &&
          <HoleForm
            positionLeft={this.props.item._positionLeft}
            positionTop={this.props.item._positionTop}
            item={this.props.item}
            onChange={this.props.onChange}
            validating={this.props.validating}
            _errors={this.props.item._errors}
          />
        }
      </fieldset>
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
