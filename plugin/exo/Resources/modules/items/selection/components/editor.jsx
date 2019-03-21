import React, {Component} from 'react'
import cloneDeep from 'lodash/cloneDeep'
import classes from 'classnames'
import Popover from 'react-bootstrap/lib/Popover'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action/components/button'
import {FormGroup} from '#/main/app/content/form/components/group'
import {FormData} from '#/main/app/content/form/containers/data'

import {makeId} from '#/main/core/scaffolding/id'
import {Textarea} from '#/main/core/layout/form/components/field/textarea'
import {ColorPicker} from '#/main/core/layout/form/components/field/color-picker'
import {CheckGroup} from '#/main/core/layout/form/components/group/check-group'

import {SCORE_SUM, SCORE_FIXED} from '#/plugin/exo/quiz/enums'
import {utils} from '#/plugin/exo/items/selection/utils/utils'
import {constants} from '#/plugin/exo/items/selection/constants'
import {ItemEditor as ItemEditorType} from '#/plugin/exo/items/prop-types'
import {SelectionItem as SelectionItemType} from '#/plugin/exo/items/selection/prop-types'

const addSelection = (begin, end, item, saveCallback) => {
  const newSolutions = item.solutions ? cloneDeep(item.solutions): []
  const newSelections = item.selections ? cloneDeep(item.selections): []
  const id = makeId()
  let sum = 0
  let text = ''
  let newItem = {}
  let cleanItem = {}

  switch(item.mode) {
    case constants.MODE_SELECT:
      sum = utils.getRealOffsetFromBegin(newSelections, begin)

      newSelections.push({
        id: id,
        begin: begin - sum,
        end: end - sum,
        _displayedBegin: begin,
        _displayedEnd: end
      })

      newSolutions.push({
        selectionId: id,
        score: 1
      })

      text = utils.getTextFromDecorated(item._text)

      newItem = Object.assign({}, item, {
        selections: newSelections,
        _selectionPopover: true,
        _selectionId: id,
        solutions: newSolutions,
        text: text,
        _text: utils.makeTextHtml(text, newSelections)
      })

      cleanItem = utils.cleanItem(newItem)
      saveCallback('solutions', cleanItem.solutions)
      saveCallback('selections', cleanItem.selections)
      saveCallback('text', cleanItem.text)
      saveCallback('_text', cleanItem._text)
      saveCallback('_selectionId', cleanItem._selectionId)
      saveCallback('_selectionPopover', cleanItem._selectionPopover)
      break
    case constants.MODE_FIND:
      sum = utils.getRealOffsetFromBegin(newSolutions, begin)

      newSolutions.push({
        selectionId: id,
        score: 1,
        begin: begin - sum,
        end: end - sum,
        _displayedBegin: begin,
        _displayedEnd: end
      })

      text = utils.getTextFromDecorated(item._text)

      newItem = Object.assign({}, item, {
        _selectionPopover: true,
        _selectionId: id,
        solutions: newSolutions,
        tries: item.tries + 1,
        text: text,
        _text: utils.makeTextHtml(text, newSolutions)
      })

      cleanItem = utils.cleanItem(newItem)
      saveCallback('solutions', cleanItem.solutions)
      saveCallback('tries', cleanItem.tries)
      saveCallback('text', cleanItem.text)
      saveCallback('_text', cleanItem._text)
      saveCallback('_selectionId', cleanItem._selectionId)
      saveCallback('_selectionPopover', cleanItem._selectionPopover)
      break
    case constants.MODE_HIGHLIGHT:
      sum = utils.getRealOffsetFromBegin(newSelections, begin)

      newSelections.push({
        id: id,
        begin: begin - sum,
        end: end - sum,
        _displayedBegin: begin,
        _displayedEnd: end
      })

      newSolutions.push({
        selectionId: id,
        answers: [{
          score: 1,
          colorId: item.colors[0].id,
          _answerId: makeId()
        }]
      })

      text = utils.getTextFromDecorated(item._text)

      newItem = Object.assign({}, item, {
        selections: newSelections,
        _selectionPopover: true,
        _selectionId: id,
        solutions: newSolutions,
        text: text,
        _text: utils.makeTextHtml(text, newSelections)
      })

      cleanItem = utils.cleanItem(newItem)
      saveCallback('solutions', cleanItem.solutions)
      saveCallback('selections', cleanItem.selections)
      saveCallback('text', cleanItem.text)
      saveCallback('_text', cleanItem._text)
      saveCallback('_selectionId', cleanItem._selectionId)
      saveCallback('_selectionPopover', cleanItem._selectionPopover)
      break
  }
}

const updateAnswer = (property, value, id, item, saveCallback) => {
  const newSolutions = cloneDeep(item.solutions)

  switch(item.mode) {
    case constants.MODE_SELECT:
    case constants.MODE_FIND:
      const solution = newSolutions.find(s => s.selectionId === id)

      if (solution) {
        solution[property] = value
      }
      break
    case constants.MODE_HIGHLIGHT:
      let answer = null

      newSolutions.forEach(s => {
        if (!answer) {
          answer = s.answers.find(answer => answer._answerId === id)
        }
      })

      if (answer) {
        answer[property] = value
      }
      break
  }

  saveCallback('solutions', newSolutions)
}

const removeSelection = (selectionId, item, saveCallback) => {
  const newSolutions = cloneDeep(item.solutions)
  let newItem = {}
  let cleanItem = {}

  switch(item.mode) {
    case constants.MODE_SELECT:
    case constants.MODE_HIGHLIGHT:
      //this is only valid for the default 'visible' one
      const newSelections = cloneDeep(item.selections)
      newSelections.splice(newSelections.findIndex(s => s.id === selectionId), 1)
      newSolutions.splice(newSolutions.findIndex(s => s.selectionId === selectionId), 1)
      newItem = Object.assign({}, item, {
        selections: newSelections,
        solutions: newSolutions,
        _text: utils.makeTextHtml(item.text, newSelections)
      })
      cleanItem = utils.cleanItem(newItem)

      saveCallback('solutions', cleanItem.solutions)
      saveCallback('selections', cleanItem.selections)
      saveCallback('_text', cleanItem._text)
      break
    case constants.MODE_FIND:
      //this is only valid for the default 'visible' one
      newSolutions.splice(newSolutions.findIndex(s => s.selectionId === selectionId), 1)
      newItem = Object.assign({}, item, {
        solutions: newSolutions,
        _text: utils.makeTextHtml(item.text, newSolutions),
        tries: item.tries - 1
      })
      cleanItem = utils.cleanItem(newItem)

      saveCallback('solutions', cleanItem.solutions)
      saveCallback('tries', cleanItem.tries)
      saveCallback('_text', cleanItem._text)
      break
  }
}

const recomputePositions = (item, offsets, oldText) => {
  if (oldText === item._text) {
    return item
  }
  let toSort = constants.MODE_FIND === item.mode ? item.solutions : item.selections

  if (!toSort) {
    return item
  }

  toSort = cloneDeep(toSort)
  toSort.sort((a, b) => a.begin - b.begin)
  let idx = 0

  toSort.forEach(element => {
    //this is where the word really start
    element._displayedBegin = utils.getHtmlLength(element) * idx + element.begin + utils.getFirstSpan(element).length
    //element._displayedBegin = getOffsets(document.getElementById(item.id))
    idx++

    const amount = item._text.length - oldText.length

    if (offsets.trueStart < element._displayedBegin) {
      element._displayedBegin += amount
      element._displayedEnd += amount
      element.begin += amount
      element.end += amount
    } else {
      //inside a div
      if (offsets.trueStart > element._displayedBegin && offsets.trueStart < element._displayedEnd) {
        element._displayedEnd += amount
        element.end += amount
      }
    }
  })

  const newData = constants.MODE_FIND === item.mode ? {solutions: toSort} : {selections: toSort}
  const newItem = Object.assign({}, item, newData)

  return newItem
}

class ChoiceItem extends Component {
  constructor(props) {
    super(props)
    this.state = {showFeedback: false}
  }

  getSelectionId() {
    return this.selectionId = this.props.selection ? this.props.selection.id: this.props.solution.selectionId
  }

  render() {
    return (
      <div className={classes(
        'answer-item keyword-item',
        {'expected-answer': this.props.score > 0},
        {'unexpected-answer': this.props.score <= 0}
      )}>
        {this.props.item.score.type === SCORE_SUM &&
          <input
            className="selection-score form-control"
            type="number"
            value={this.props.score}
            step="0.5"
            onChange={(e) => updateAnswer('score', Number(e.target.value), this.getSelectionId(), this.props.item, this.props.update)}
          />
        }

        {this.props.item.score.type === SCORE_FIXED &&
          <span>
            <input
              type="checkbox"
              id={'selection-chk-' + this.getSelectionId()}
              checked={this.props.score > 0}
              onChange={(e) => updateAnswer('score', e.target.checked ? 1 : 0, this.getSelectionId(), this.props.item, this.props.update)}
            />
            {'\u00a0'}
            <span>
              {trans('correct_answer', {}, 'quiz')}
            </span>
          </span>
        }

        <Button
          id={`choice-${this.getSelectionId()}-feedback-toggle`}
          className="btn pull-right"
          type={CALLBACK_BUTTON}
          icon="fa fa-fw fa-comments-o"
          label={trans('choice_feedback_info', {}, 'quiz')}
          callback={() => this.setState({showFeedback: !this.state.showFeedback})}
          tooltip="top"
        />

        {this.state.showFeedback &&
          <div className="feedback-container selection-form-row">
            <Textarea
              id={`choice-${this.getSelectionId()}-feedback`}
              value={this.props.solution.feedback}
              onChange={(text) => updateAnswer('feedback', text, this.getSelectionId(), this.props.item, this.props.update)}
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
  },
  score: 0
}

ChoiceItem.propTypes = {
  item: T.shape(SelectionItemType.propTypes).isRequired,
  selection: T.shape({
    id: T.string.isRequired
  }),
  solution: T.shape({
    selectionId: T.string.isRequired,
    feedback: T.string
  }),
  score: T.number.isRequired,
  update: T.func.isRequired
}

class SelectionForm extends Component {
  constructor(props) {
    super(props)
    this.state = {showFeedback: false}
  }

  getSelection() {
    return this.props.item.selections ?
      this.props.item.selections.find(selection => selection.id === this.props.item._selectionId) :
      {id: this.props.item._selectionId}
  }

  getSolution() {
    return this.props.item.solutions.find(solution => solution.selectionId === this.props.item._selectionId)
  }

  closePopover() {
    this.props.update('_selectionPopover', false)
  }

  removeAndClose() {
    removeSelection(this.props.item._selectionId, this.props.item, this.props.update)
    this.closePopover()
  }

  render() {
    // Let's calculate the popover position
    // It will be positioned just under the edit button
    const btnElement = document.querySelector(`.edit-selection-btn[data-selection-id="${this.props.item._selectionId}"]`)

    let left = btnElement ? btnElement.offsetLeft : 0
    let top  = btnElement ? btnElement.offsetTop : 0

    left += btnElement ? btnElement.offsetWidth / 2 : 0 // center popover and edit btn
    top  += btnElement ? btnElement.offsetHeight : 0 // position popover below edit btn

    left -= 180 // half size of the popover

    switch (this.props.item.mode) {
      case constants.MODE_SELECT:
        top += 89
        break
      case constants.MODE_HIGHLIGHT:
        top += 244

        this.props.item.colors.forEach(() => top += 19)
        break
      case constants.MODE_FIND:
        top += 235
        break
    }

    if (this.props.item.score.type === SCORE_FIXED) {
      top += 147

      if (this.props.item.mode !== constants.MODE_SELECT)  {
        top -= 75
      }
    }

    return (
      <Popover
        id={this.props.item._selectionId}
        positionLeft={left}
        positionTop={top}
        placement="bottom"
        title={
          <div>
            {utils.getSelectionText(this.props.item)}

            <div className="popover-actions">
              <Button
                id={`selection-${this.props.item._selectionId}-delete`}
                className="btn-link"
                type={CALLBACK_BUTTON}
                icon="fa fa-fw fa-trash-o"
                label={trans('delete', {}, 'quiz')}
                callback={this.removeAndClose.bind(this)}
                tooltip="top"
              />

              <Button
                id={`selection-${this.props.item._selectionId}-close`}
                className="btn-link"
                type={CALLBACK_BUTTON}
                icon="fa fa-fw fa-times"
                label={trans('close', {}, 'quiz')}
                callback={this.closePopover.bind(this)}
                tooltip="top"
              />
            </div>
          </div>
        }>
        {-1 < [constants.MODE_SELECT, constants.MODE_FIND].indexOf(this.props.item.mode) && this.getSolution() &&
          <ChoiceItem
            score={this.getSolution().score}
            selection={this.getSelection()}
            solution={this.getSolution()}
            item={this.props.item}
            update={this.props.update}
          />
        }
        {this.props.item.mode === constants.MODE_HIGHLIGHT && this.getSolution() &&
          this.getSolution().answers.map((answer, key) =>
            <HighlightAnswer
              key={key}
              answer={answer}
              item={this.props.item}
              update={this.props.update}
            />
          )
        }
        {this.props.item.mode === constants.MODE_HIGHLIGHT && this.getSolution() &&
          <Button
            id="add-solution-color-btn"
            className="btn btn-default"
            type={CALLBACK_BUTTON}
            icon="fa fa-fw fa-plus"
            label={trans('color')}
            disabled={this.getSolution().answers.length >= this.props.item.colors.length}
            callback={() => {
              const newSolutions = cloneDeep(this.props.item.solutions)
              const solution = newSolutions.find(s => s.selectionId === this.props.item._selectionId)

              if (solution) {
                solution.answers.push({score: 0, colorId: this.props.item.colors[0].id, _answerId: makeId()})
                this.props.update('solutions', newSolutions)
              }
            }}
          />
        }

        {this.state.showFeedback &&
          <div className="feedback-container selection-form-row">
            <Textarea
              id={`choice-${this.props.item._selectionId}-feedback`}
              value={this.props.item.feedback}
              onChange={(text) => updateAnswer('feedback', text, this.props.item._selectionId, this.props.item, this.props.update)}
            />
          </div>
        }
      </Popover>
    )
  }
}

SelectionForm.propTypes = {
  item: T.shape(SelectionItemType.propTypes).isRequired,
  update: T.func.isRequired
}

class ColorElement extends Component {
  render() {
    return (
      <div>
        <ColorPicker
          id={`color-${this.props.index}`}
          value={this.props.color.code}
          onChange={(colorCode) => {
            const newColors = cloneDeep(this.props.item.colors)
            const color = newColors.find(c => c.id === this.props.color.id)

            if (color) {
              color.code = colorCode
              this.props.update('colors', newColors)
            }
          }}
          autoOpen={this.props.autoOpen}
        />
        {'\u00a0'}
        <span
          className="fa fa-trash-o pointer"
          onClick={() => {
            const newColors = cloneDeep(this.props.item.colors)
            newColors.splice(newColors.findIndex(c => c.id === this.props.color.id), 1)

            const newSolutions = cloneDeep(this.props.item.solutions)
            newSolutions.forEach(s => s.answers.splice(s.answers.findIndex(a => a.colorId === this.props.color.id)))

            this.props.update('colors', newColors)
            this.props.update('solutions', newSolutions)
          }}
        />
      </div>
    )
  }
}

ColorElement.propTypes = {
  item: T.shape(SelectionItemType.propTypes).isRequired,
  index: T.number.isRequired,
  color: T.shape({
    code: T.string.isRequired,
    id: T.string.isRequired
  }),
  autoOpen: T.bool.isRequired,
  update: T.func.isRequired
}

class HighlightAnswer extends Component {
  constructor(props) {
    super(props)
    this.state = {showFeedback: false}
  }

  render() {
    const color = this.props.item.colors.find(color => color.id === this.props.answer.colorId)

    return (
      <div className={classes(
        'answer-item keyword-item',
        {'expected-answer': this.props.answer.score > 0},
        {'unexpected-answer': this.props.answer.score <= 0}
      )}>
        <div className='row'>
          <div className="col-xs-3">
            <select className="color-select checkbox"
              style={{ backgroundColor: color.code, verticalAlign: 'center', display: 'inline-block' }}
              onChange={(e) => updateAnswer('colorId', e.target.value, this.props.answer._answerId, this.props.item, this.props.update)}
              value={this.props.answer.colorId}
            >
              {this.props.item.colors.map((color, key) => {
                return <option
                  className="color-option"
                  key={key}
                  value={color.id}
                  style={{ backgroundColor: color.code, hover: color.code }}
                >
                  {'\u00a0'}{'\u00a0'}{'\u00a0'}{'\u00a0'}{'\u00a0'}
                </option>
              })}
            </select>
          </div>
          <div className="col-xs-4">
            {this.props.item.score.type === SCORE_SUM &&
              <input
                type="number"
                step="0.5"
                onChange={(e) => updateAnswer('score', Number(e.target.value), this.props.answer._answerId, this.props.item, this.props.update)}
                value={this.props.answer.score}
                className="form-control keyword-score"
              />
            }
            {this.props.item.score.type === SCORE_FIXED &&
              <CheckGroup
                id={this.props.answer._answerId}
                label=""
                value={this.props.answer.score > 0}
                onChange={(checked) => updateAnswer('score', checked ? 1 : 0, this.props.answer._answerId, this.props.item, this.props.update)}
              />
            }
          </div>
          <div className="col-xs-2">
            <Button
              id={`choice-${this.props.answer._answerId}-feedback-toggle`}
              className="btn"
              type={CALLBACK_BUTTON}
              icon="fa fa-fw fa-comments-o"
              label={trans('choice_feedback_info', {}, 'quiz')}
              callback={() => this.setState({showFeedback: !this.state.showFeedback})}
              tooltip="top"
            />
          </div>
          <div className="col-xs-3">
            <i
              className="fa fa-trash-o pointer checkbox"
              onClick={() => {
                const newSolutions = cloneDeep(this.props.item.solutions)
                const solution = newSolutions.find(s => s.selectionId === this.props.item._selectionId)

                if (solution) {
                  solution.answers.splice(solution.answers.findIndex(a => a._answerId === this.props.answer._answerId), 1)
                  this.props.update('solutions', newSolutions)
                }
              }}
            ></i>
          </div>
        </div>
        {this.state.showFeedback &&
          <div className="feedback-container selection-form-row">
            <Textarea
              id={`choice-${this.props.answer._answerId}-feedback`}
              onChange={(text) => updateAnswer('feedback', text, this.props.answer._answerId, this.props.item, this.props.update)}
              value={this.props.answer.feedback}
            />
          </div>
        }
      </div>
    )
  }
}

HighlightAnswer.propTypes = {
  item: T.shape(SelectionItemType.propTypes).isRequired,
  update: T.func.isRequired,
  answer: T.shape({
    colorId: T.string.isRequired,
    _answerId: T.string,
    score: T.number.isRequired,
    feedback: T.string
  })
}

class SelectionText extends Component {
  constructor(props) {
    super(props)
    this.onSelect = this.onSelect.bind(this)
    this.updateText = this.updateText.bind(this)
    this.addSelection = this.addSelection.bind(this)
    this.state = {
      trueStart: null,
      trueEnd: null,
      start: null,
      end: null,
      allowSelection: true
    }
    this.changeEditorMode = this.changeEditorMode.bind(this)
    this.isSelectionCreationAllowed = this.isSelectionCreationAllowed.bind(this)
    this.onSelectionClick = this.onSelectionClick.bind(this)
  }

  updateText() {
    utils.makeTextHtml(this.props.item._text, this.props.item.solutions)
  }

  onSelect(selected, cb, offsets) {
    if (offsets) {
      this.setState({
        trueStart: offsets.trueStart,
        trueEnd: offsets.trueEnd,
        start: offsets.start,
        end: offsets.end
      })
    }
  }

  changeEditorMode(editorState) {
    this.setState({ allowSelection: editorState.minimal})
  }

  addSelection(item) {
    addSelection(this.state.trueStart, this.state.trueEnd, item, this.props.update)
  }

  onSelectionClick(el) {
    if (el.classList.contains('edit-selection-btn')) {
      this.props.update('_selectionPopover', true)
      this.props.update('_selectionId', el.dataset.selectionId)
    } else {
      if (el.classList.contains('delete-selection-btn')) {
        removeSelection(el.dataset.selectionId, this.props.item, this.props.update)
      }
    }
  }

  isSelectionCreationAllowed() {
    let allowed = this.state.trueStart !== this.state.trueEnd

    if (!allowed) {
      return false
    }

    const elements = constants.MODE_FIND === this.props.item.mode ? this.props.item.solutions : this.props.item.selections

    if (elements) {
      elements.forEach(element => {
        if (
          (this.state.trueStart >= element._displayedBegin && this.state.trueStart <= element._displayedEnd) ||
          (this.state.trueEnd >= element._displayedBegin && this.state.trueEnd <= element._displayedEnd) ||
          (this.state.trueStart <= element._displayedBegin && this.state.trueEnd >= element._displayedEnd)
        ) {
          allowed = false
        }
      })
    }

    return allowed && this.state.allowSelection
  }

  render() {
    return (
      <div>
        <FormGroup
          id="selection-text-box"
          label=""
        >
          <Textarea
            id={`selection-text-${this.props.item.id}`}
            onSelect={this.onSelect}
            onChange={(text, offsets) => {
              // we need to update the positions here because if we add text BEFORE our marks, then everything is screwed up
              const newItem = Object.assign({}, this.props.item, {
                text: utils.getTextFromDecorated(text),
                _text: text
              })
              const positionItem = recomputePositions(newItem, offsets, this.props.item._text)
              const cleanItem = utils.cleanItem(positionItem)
              this.props.update('solutions', cleanItem.solutions ? cleanItem.solutions : [])
              this.props.update('selections', cleanItem.selections ? cleanItem.selections : [])
              this.props.update('text', cleanItem.text)
              this.props.update('_text', cleanItem._text)
            }}
            onClick={this.onSelectionClick}
            value={this.props.item._text}
            updateText={this.updateText}
            onChangeMode={this.changeEditorMode}
          />
        </FormGroup>

        <Button
          id="add-selection-btn"
          className="btn"
          type={CALLBACK_BUTTON}
          icon="fa fa-fw fa-plus"
          label={trans('create_selection_zone', {}, 'quiz')}
          disabled={!this.isSelectionCreationAllowed()}
          callback={() => this.addSelection(this.props.item)}
        />

        {this.props.item._selectionPopover &&
          <SelectionForm
            item={this.props.item}
            update={this.props.update}
          />
        }
      </div>
    )
  }
}

SelectionText.propTypes = {
  item: T.shape(SelectionItemType.propTypes).isRequired,
  update: T.func.isRequired
}

const SelectionEditor = (props) =>
  <FormData
    className="selection-item selection-editor"
    embedded={true}
    name={props.formName}
    dataPart={props.path}
    sections={[
      {
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'fixedScore',
            label: trans('score_fixed', {}, 'quiz'),
            type: 'boolean',
            onChange: (checked) => props.update('score', Object.assign({}, props.item.score, {type: checked ? SCORE_FIXED : SCORE_SUM})),
            linked: [
              {
                name: 'score.success',
                type: 'number',
                label: trans('score_fixed_success', {}, 'quiz'),
                required: SCORE_FIXED === props.item.score.type,
                options: {
                  min: 0
                },
                displayed: (item) => SCORE_FIXED === item.score.type
              }, {
                name: 'score.failure',
                type: 'number',
                label: trans('score_fixed_failure', {}, 'quiz'),
                required: SCORE_FIXED === props.item.score.type,
                displayed: (item) => SCORE_FIXED === item.score.type
              }
            ]
          }, {
            name: 'mode',
            label: trans('mode'),
            type: 'choice',
            required: true,
            hideLabel: true,
            options: {
              choices: constants.MODE_CHOICES
            },
            onChange: (value) => {
              const newSolutions = props.item.solutions ? cloneDeep(props.item.solutions) : []
              const selections = []

              switch (value) {
                case constants.MODE_SELECT:
                  if (!props.item.selections) {
                    props.item.solutions.forEach(s => selections.push({
                      id: s.selectionId,
                      begin: s.begin,
                      end: s.end,
                      _displayedBegin: s._displayedBegin,
                      _displayedEnd: s._displayedEnd
                    }))

                    props.update('selections', selections)
                  }
                  //remove colors
                  props.update('colors', [])
                  break
                case constants.MODE_FIND:
                  //add beging and end to solutions
                  newSolutions.forEach(solution => {
                    let selection = props.item.selections.find(s => s.id === solution.selectionId)

                    if (selection) {
                      solution.begin = selection.begin
                      solution.end = selection.end
                      solution._displayedBegin = selection._displayedBegin,
                      solution._displayedEnd = selection._displayedEnd
                      solution.score = solution.score || 0
                    }
                  })

                  props.update('solutions', newSolutions)
                  props.update('tries', newSolutions.filter(s => 0 < s.score).length)
                  props.update('selections', [])
                  props.update('colors', [])
                  break
                case constants.MODE_HIGHLIGHT:
                  if (!props.item.selections) {
                    newSolutions.forEach(s => selections.push({
                      id: s.selectionId,
                      begin: s.begin,
                      end: s.end,
                      _displayedBegin: s._displayedBegin,
                      _displayedEnd: s._displayedEnd
                    }))

                    props.update('selections', selections)
                  }
                  newSolutions.forEach(s => s.answers = [])

                  props.update('solutions', newSolutions)
                  props.update('colors', [{
                    id: makeId(),
                    _autoOpen: false,
                    code: '#'+(Math.random()*0xFFFFFF<<0).toString(16)
                  }])
                  break
              }
            },
            linked: [
              {
                name: 'tries',
                type: 'number',
                label: trans('tries_number', {}, 'quiz'),
                options: {
                  min: props.item.solutions ? props.item.solutions.filter(s => 0 < s.score).length : 1
                },
                displayed: (item) => constants.MODE_FIND === item.mode
              }, {
                name: 'penalty',
                type: 'number',
                label: trans('global_penalty', {}, 'quiz'),
                options: {
                  min: 0
                },
                displayed: (item) => SCORE_SUM === item.score.type &&
                  -1 < [constants.MODE_FIND, constants.MODE_HIGHLIGHT].indexOf(item.mode)
              }, {
                name: 'selectionColors',
                label: trans('selection_colors', {}, 'quiz'),
                hideLabel: true,
                displayed: (item) => constants.MODE_HIGHLIGHT === item.mode,
                render: (selectionItem, selectionErrors) => {
                  return (
                    <div>
                      <div>{trans('possible_color_choices', {}, 'quiz')}</div>
                      {selectionItem.colors && selectionItem.colors.map((color, index) =>
                        <ColorElement
                          key={'color' + index}
                          item={selectionItem}
                          index={index}
                          color={color}
                          update={props.update}
                          autoOpen={color._autoOpen}
                        />
                      )}
                      <Button
                        id="add-color-btn"
                        className="btn btn-default"
                        type={CALLBACK_BUTTON}
                        icon="fa fa-fw fa-plus"
                        label={trans('add_color', {}, 'quiz')}
                        callback={() => {
                          const newColors = cloneDeep(selectionItem.colors)
                          newColors.push({
                            id: makeId(),
                            code: '#'+(Math.random()*0xFFFFFF<<0).toString(16),
                            _autoOpen: true
                          })
                          props.update('colors', newColors)
                        }}
                      />
                    </div>
                  )
                }
              }
            ]
          }, {
            name: 'selections',
            label: trans('selections', {}, 'quiz'),
            hideLabel: true,
            required: true,
            render: (selectionItem, selectionErrors) => {
              return (
                <SelectionText
                  item={selectionItem}
                  update={props.update}
                />
              )
            },
            validate: (selectionItem) => {
              return undefined
            }
          }
        ]
      }
    ]}
  />

implementPropTypes(SelectionEditor, ItemEditorType, {
  item: T.shape(SelectionItemType.propTypes).isRequired
})

export {SelectionEditor}
