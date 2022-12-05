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
import {HtmlInput} from '#/main/app/data/types/html/components/input'
import {ColorInput} from '#/main/theme/data/types/color/components/input'
import {CheckGroup} from '#/main/core/layout/form/components/group/check-group'

import {SCORE_SUM} from '#/plugin/exo/quiz/enums'
import {utils} from '#/plugin/exo/items/selection/utils/utils'
import {constants} from '#/plugin/exo/items/selection/constants'
import {ItemEditor as ItemEditorType} from '#/plugin/exo/items/prop-types'
import {SelectionItem as SelectionItemType} from '#/plugin/exo/items/selection/prop-types'

const addSelection = (begin, end, item, _text, saveCallback) => {
  const newSolutions = item.solutions ? cloneDeep(item.solutions): []
  const newSelections = item.selections ? cloneDeep(item.selections): []
  const id = makeId()
  let sum = 0
  let text = ''

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

      text = utils.getTextFromDecorated(_text)

      saveCallback('solutions', newSolutions)
      saveCallback('selections', newSelections)
      saveCallback('text', text)

      return {
        selectionId: id,
        text: utils.makeTextHtml(text, newSelections)
      }
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

      text = utils.getTextFromDecorated(_text)

      saveCallback('solutions', newSolutions)
      saveCallback('tries', item.tries + 1)
      saveCallback('text', text)

      return {
        selectionId: id,
        text: utils.makeTextHtml(text, newSolutions)
      }
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

      text = utils.getTextFromDecorated(_text)

      saveCallback('solutions', newSolutions)
      saveCallback('selections', newSelections)
      saveCallback('text', text)

      return {
        selectionId: id,
        text: utils.makeTextHtml(text, newSelections)
      }
  }
}

const updateAnswer = (property, value, id, item, saveCallback) => {
  const newSolutions = cloneDeep(item.solutions)

  let answer = null
  let solution = null

  switch (item.mode) {
    case constants.MODE_SELECT:
    case constants.MODE_FIND:
      solution = newSolutions.find(s => s.selectionId === id)

      if (solution) {
        solution[property] = value
      }
      break

    case constants.MODE_HIGHLIGHT:
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

const removeSelection = (selectionId, item, _text, saveCallback) => {
  const newSolutions = cloneDeep(item.solutions)
  let newItem = {}
  let cleanItem = {}
  let newSelections = []

  switch (item.mode) {
    case constants.MODE_SELECT:
    case constants.MODE_HIGHLIGHT:
      //this is only valid for the default 'visible' one
      newSelections = cloneDeep(item.selections)
      newSelections.splice(newSelections.findIndex(s => s.id === selectionId), 1)
      newSolutions.splice(newSolutions.findIndex(s => s.selectionId === selectionId), 1)
      newItem = Object.assign({}, item, {
        selections: newSelections,
        solutions: newSolutions
      })
      cleanItem = utils.cleanItem(newItem, _text)

      saveCallback('solutions', cleanItem.solutions)
      saveCallback('selections', cleanItem.selections)

      return {
        text: utils.makeTextHtml(item.text, newSelections)
      }
    case constants.MODE_FIND:
      //this is only valid for the default 'visible' one
      newSolutions.splice(newSolutions.findIndex(s => s.selectionId === selectionId), 1)
      newItem = Object.assign({}, item, {
        solutions: newSolutions,
        tries: item.tries - 1
      })
      cleanItem = utils.cleanItem(newItem, _text)

      saveCallback('solutions', cleanItem.solutions)
      saveCallback('tries', cleanItem.tries)

      return {
        text: utils.makeTextHtml(item.text, newSolutions)
      }
  }
}

const recomputePositions = (item, offsets, oldText, text) => {
  if (oldText === text) {
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

    const amount = text.length - oldText.length

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
        'answer-item', this.props.item.hasExpectedAnswers && {
          'expected-answer': this.props.score > 0,
          'unexpected-answer': this.props.score <= 0
        }
      )}>
        <div className="keyword-item">
          {this.props.item.hasExpectedAnswers && this.props.hasScore &&
            <input
              className="selection-score form-control"
              type="number"
              value={this.props.score}
              step="0.5"
              onChange={(e) => updateAnswer('score', Number(e.target.value), this.getSelectionId(), this.props.item, this.props.update)}
            />
          }

          {this.props.item.hasExpectedAnswers && !this.props.hasScore &&
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
            icon="fa fa-fw fa-comments"
            label={trans('choice_feedback_info', {}, 'quiz')}
            callback={() => this.setState({showFeedback: !this.state.showFeedback})}
            tooltip="top"
          />
        </div>

        {this.state.showFeedback &&
          <div className="feedback-container selection-form-row">
            <HtmlInput
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
  update: T.func.isRequired,
  hasScore: T.bool.isRequired
}

class SelectionForm extends Component {
  constructor(props) {
    super(props)
    this.state = {showFeedback: false}
  }

  getSelection() {
    return this.props.item.selections ?
      this.props.item.selections.find(selection => selection.id === this.props.selectionId) :
      {id: this.props.selectionId}
  }

  getSolution() {
    return this.props.item.solutions.find(solution => solution.selectionId === this.props.selectionId)
  }

  render() {
    // Let's calculate the popover position
    // It will be positioned just under the edit button
    const btnElement = document.querySelector(`.edit-selection-btn[data-selection-id="${this.props.selectionId}"]`)

    let left = btnElement ? btnElement.offsetLeft : 0
    let top  = btnElement ? btnElement.offsetTop : 0

    left += btnElement ? btnElement.offsetWidth / 2 : 0 // center popover and edit btn
    top  += btnElement ? btnElement.offsetHeight : 0 // position popover below edit btn

    left -= 180 // half size of the popover

    switch (this.props.item.mode) {
      case constants.MODE_SELECT:
        top += 75
        break
      case constants.MODE_HIGHLIGHT:
        top += 230

        this.props.item.colors.forEach(() => top += 25)
        break
      case constants.MODE_FIND:
        top += 221
        break
    }

    if (this.props.item.score.type !== SCORE_SUM && this.props.item.mode !== constants.MODE_SELECT) {
      top -= 75
    }

    return (
      <Popover
        id={this.props.selectionId}
        positionLeft={left}
        positionTop={top}
        placement="bottom"
        title={
          <div>
            {utils.getSelectionText(this.props.item, this.props.selectionId)}

            <div className="popover-actions">
              <Button
                id={`selection-${this.props.selectionId}-delete`}
                className="btn-link"
                type={CALLBACK_BUTTON}
                icon="fa fa-fw fa-trash"
                label={trans('delete', {}, 'quiz')}
                callback={this.props.onRemove}
                tooltip="top"
              />

              <Button
                id={`selection-${this.props.selectionId}-close`}
                className="btn-link"
                type={CALLBACK_BUTTON}
                icon="fa fa-fw fa-times"
                label={trans('close', {}, 'quiz')}
                callback={this.props.onClose}
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
            hasScore={this.props.hasScore}
          />
        }
        {this.props.item.mode === constants.MODE_HIGHLIGHT && this.getSolution() &&
          this.getSolution().answers.map((answer, key) =>
            <HighlightAnswer
              key={key}
              answer={answer}
              item={this.props.item}
              selectionId={this.props.selectionId}
              update={this.props.update}
              hasScore={this.props.hasScore}
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
              const solution = newSolutions.find(s => s.selectionId === this.props.selectionId)

              if (solution) {
                solution.answers.push({score: 0, colorId: this.props.item.colors[0].id, _answerId: makeId()})
                this.props.update('solutions', newSolutions)
              }
            }}
          />
        }

        {this.state.showFeedback &&
          <div className="feedback-container selection-form-row">
            <HtmlInput
              id={`choice-${this.props.selectionId}-feedback`}
              value={this.props.item.feedback}
              onChange={(text) => updateAnswer('feedback', text, this.props.selectionId, this.props.item, this.props.update)}
            />
          </div>
        }
      </Popover>
    )
  }
}

SelectionForm.propTypes = {
  item: T.shape(SelectionItemType.propTypes).isRequired,
  selectionId: T.string.isRequired,
  hasScore: T.bool.isRequired,
  update: T.func.isRequired,
  onClose: T.func.isRequired,
  onRemove: T.func.isRequired
}

class ColorElement extends Component {
  render() {
    return (
      <div>
        <ColorInput
          id={`color-${this.props.index}`}
          value={this.props.color.code}
          hideInput={true}
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
          className="fa fa-trash pointer"
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
        'answer-item', this.props.item.hasExpectedAnswers && {
          'expected-answer': this.props.answer.score > 0,
          'unexpected-answer': this.props.answer.score <= 0
        }
      )}>
        <div className='keyword-item'>
          <div className={this.props.item.hasExpectedAnswers ? 'col-xs-3' : 'col-xs-4'}>
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
          {this.props.item.hasExpectedAnswers &&
            <div className="col-xs-4">
              {this.props.hasScore &&
                <input
                  type="number"
                  step="0.5"
                  onChange={(e) => updateAnswer('score', Number(e.target.value), this.props.answer._answerId, this.props.item, this.props.update)}
                  value={this.props.answer.score}
                  className="form-control keyword-score"
                />
              }
              {!this.props.hasScore &&
                <CheckGroup
                  id={this.props.answer._answerId}
                  label=""
                  value={this.props.answer.score > 0}
                  onChange={(checked) => updateAnswer('score', checked ? 1 : 0, this.props.answer._answerId, this.props.item, this.props.update)}
                />
              }
            </div>
          }
          <div className={this.props.item.hasExpectedAnswers ? 'col-xs-2' : 'col-xs-4'}>
            <Button
              id={`choice-${this.props.answer._answerId}-feedback-toggle`}
              className="btn"
              type={CALLBACK_BUTTON}
              icon="fa fa-fw fa-comments"
              label={trans('choice_feedback_info', {}, 'quiz')}
              callback={() => this.setState({showFeedback: !this.state.showFeedback})}
              tooltip="top"
            />
          </div>
          <div className={this.props.item.hasExpectedAnswers ? 'col-xs-3' : 'col-xs-4'}>
            <span
              className="fa fa-trash pointer checkbox"
              onClick={() => {
                const newSolutions = cloneDeep(this.props.item.solutions)
                const solution = newSolutions.find(s => s.selectionId === this.props.selectionId)

                if (solution) {
                  solution.answers.splice(solution.answers.findIndex(a => a._answerId === this.props.answer._answerId), 1)
                  this.props.update('solutions', newSolutions)
                }
              }}
            />
          </div>
        </div>
        {this.state.showFeedback &&
          <div className="feedback-container selection-form-row">
            <HtmlInput
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
  selectionId: T.string.isRequired,
  update: T.func.isRequired,
  answer: T.shape({
    colorId: T.string.isRequired,
    _answerId: T.string,
    score: T.number.isRequired,
    feedback: T.string
  }),
  hasScore: T.bool.isRequired
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
      allowSelection: true,
      text: undefined,
      selectionId: null,
      selectionPopover : false
    }
    this.changeEditorMode = this.changeEditorMode.bind(this)
    this.isSelectionCreationAllowed = this.isSelectionCreationAllowed.bind(this)
    this.onSelectionClick = this.onSelectionClick.bind(this)
  }

  updateText() {
    utils.makeTextHtml(this.state.text, this.props.item.solutions)
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
    const data = addSelection(this.state.trueStart, this.state.trueEnd, item, this.state.text, this.props.update)
    this.setState({text: data.text, selectionId: data.selectionId}, () => this.setState({selectionPopover: true}))
  }

  onSelectionClick(el) {
    if (el.classList.contains('edit-selection-btn')) {
      this.setState({selectionId: el.dataset.selectionId, selectionPopover: true})
    } else {
      if (el.classList.contains('delete-selection-btn')) {
        const data = removeSelection(el.dataset.selectionId, this.props.item, this.state.text, this.props.update)
        this.setState({text: data.text})
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
    if (this.props.item.text && undefined === this.state.text) {
      let data = this.props.item.mode === constants.MODE_FIND ? this.props.item.solutions : this.props.item.selections

      if (!data) {
        data = []
      }
      this.setState({text: utils.makeTextHtml(this.props.item.text, data)})

      if (constants.MODE_HIGHLIGHT === this.props.item.mode) {
        const solutions = cloneDeep(this.props.item.solutions)

        solutions.forEach(solution => {
          let answers = []
          solution.answers.forEach(answer => {
            answers.push(Object.assign({}, answer, {_answerId: makeId()}))
          })
          solution.answers = answers
        })

        const colors = cloneDeep(this.props.item.colors)

        colors.forEach(color => {
          color._autoOpen = false
        })

        this.props.update('solutions', solutions)
        this.props.update('colors', colors)
      }

      //setting true positions here
      const sol = constants.MODE_FIND === this.props.item.mode ? this.props.item.solutions : this.props.item.selections

      if (sol) {
        const toSort = cloneDeep(sol)
        toSort.sort((a, b) => a.begin - b.begin)
        let idx = 0

        toSort.forEach(element => {
          //this is where the word really start
          let begin = utils.getHtmlLength(element) * idx + element.begin + utils.getFirstSpan(element).length
          let selection = utils.getSelectionText(this.props.item, element.selectionId || element.id)
          element._displayedBegin = begin
          element._displayedEnd = begin + selection.length
          idx++
        })

        if (constants.MODE_FIND === this.props.item.mode) {
          this.props.update('solutions', toSort)
        } else {
          this.props.update('selections', toSort)
        }
      }
    }

    return (
      <div>
        <FormGroup
          id="selection-text-box"
          label=""
        >
          <HtmlInput
            id={`selection-text-${this.props.item.id}`}
            onSelect={this.onSelect}
            onChange={(text, offsets) => {
              // we need to update the positions here because if we add text BEFORE our marks, then everything is screwed up
              const newItem = Object.assign({}, this.props.item, {
                text: utils.getTextFromDecorated(text)
              })
              const positionItem = recomputePositions(newItem, offsets, this.state.text, text)
              const cleanItem = utils.cleanItem(positionItem, text)
              this.props.update('solutions', cleanItem.solutions ? cleanItem.solutions : [])
              this.props.update('selections', cleanItem.selections ? cleanItem.selections : [])
              this.props.update('text', cleanItem.text)

              this.setState({text: text})
            }}
            onClick={this.onSelectionClick}
            value={this.state.text}
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

        {this.state.selectionPopover &&
          <SelectionForm
            item={this.props.item}
            selectionId={this.state.selectionId}
            update={this.props.update}
            onClose={() => this.setState({selectionPopover: false})}
            onRemove={() => {
              const data = removeSelection(this.state.selectionId, this.props.item, this.state.text, this.props.update)
              this.setState({text: data.text, selectionPopover: false})
            }}
            hasScore={this.props.hasScore}
          />
        }
      </div>
    )
  }
}

SelectionText.propTypes = {
  item: T.shape(SelectionItemType.propTypes).isRequired,
  update: T.func.isRequired,
  hasScore: T.bool.isRequired
}

const SelectionEditor = (props) => {
  const Selection = (
    <SelectionText
      item={props.item}
      update={props.update}
      hasScore={props.hasAnswerScores}
    />
  )

  return (
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
                const colorId = makeId()

                switch (value) {
                  case constants.MODE_SELECT:
                    if (!props.item.selections || 0 === props.item.selections.length) {
                      newSolutions.forEach(solution => selections.push({
                        id: solution.selectionId,
                        begin: solution.begin,
                        end: solution.end,
                        _displayedBegin: solution._displayedBegin,
                        _displayedEnd: solution._displayedEnd
                      }))

                      props.update('selections', selections)
                    }
                    // check score
                    newSolutions.forEach(solution => {
                      solution.score = solution.score || 1
                    })
                    props.update('solutions', newSolutions)
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
                        solution.score = solution.score || 1
                      }
                    })

                    props.update('solutions', newSolutions)
                    props.update('tries', newSolutions.filter(s => 0 < s.score).length)
                    props.update('selections', [])
                    props.update('colors', [])
                    break
                  case constants.MODE_HIGHLIGHT:
                    props.update('colors', [{
                      id: colorId,
                      _autoOpen: false,
                      code: '#'+(Math.random()*0xFFFFFF<<0).toString(16)
                    }])

                    if (!props.item.selections || 0 === props.item.selections.length) {
                      newSolutions.forEach(s => selections.push({
                        id: s.selectionId,
                        begin: s.begin,
                        end: s.end,
                        _displayedBegin: s._displayedBegin,
                        _displayedEnd: s._displayedEnd
                      }))

                      props.update('selections', selections)
                    }
                    newSolutions.forEach(s => {
                      s.answers = [{
                        score: 1,
                        colorId: colorId,
                        _answerId: makeId()
                      }]
                      delete s.score
                    })

                    props.update('solutions', newSolutions)
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
                  render: (selectionItem) => {
                    const SelectionColors = (
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

                    return SelectionColors
                  }
                }
              ]
            }, {
              name: 'selections',
              label: trans('selections', {}, 'quiz'),
              hideLabel: true,
              required: true,
              component: Selection
            }
          ]
        }
      ]}
    />
  )
}

implementPropTypes(SelectionEditor, ItemEditorType, {
  item: T.shape(SelectionItemType.propTypes).isRequired
})

export {SelectionEditor}
