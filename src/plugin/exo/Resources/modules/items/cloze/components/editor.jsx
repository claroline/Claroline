import React, {Component} from 'react'
import get from 'lodash/get'
import cloneDeep from 'lodash/cloneDeep'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {theme} from '#/main/theme/config'
import {makeId} from '#/main/core/scaffolding/id'
import {trans} from '#/main/app/intl/translation'
import {FormGroup} from '#/main/app/content/form/components/group'
import {HtmlInput} from '#/main/app/data/types/html/components/input'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

import {ItemEditor as ItemEditorTypes} from '#/plugin/exo/items/prop-types'
import {ClozeItem as ClozeItemTypes} from '#/plugin/exo/items/cloze/prop-types'
import {KeywordsPopover} from '#/plugin/exo/components/keywords'
import {keywords as keywordsUtils} from '#/plugin/exo/utils/keywords'
import {utils} from '#/plugin/exo/items/cloze/utils'

function getHoleFromId(item, holeId) {
  return item.holes.find(hole => hole.id === holeId)
}

function updateHoleChoices(hole, holeSolution) {
  if (hole._multiple) {
    hole.choices = holeSolution.answers.map(answer => answer.text)
  } else {
    delete hole.choices
  }
}

function getSolutionFromHole(solutions, hole) {
  return solutions.find(solution => solution.holeId === hole.id)
}

const HolePopover = props => {
  // Let's calculate the popover position
  // It will be positioned just under the edit button
  const btnElement = document.querySelector(`.cloze-hole[data-hole-id="${props.hole.id}"] .edit-hole-btn`)

  let left = btnElement ? btnElement.offsetLeft : 0
  let top  = btnElement ? btnElement.offsetTop : 0

  if (btnElement) {
    left += btnElement.offsetWidth / 2 // center popover and edit btn
    top  += btnElement.offsetHeight // position popover below edit btn
  }

  left -= 180 // half size of the popover
  top  += 25 // take into account the form group label

  return (
    <KeywordsPopover
      id={props.hole.id}
      positionLeft={left}
      positionTop={top}
      title={trans('cloze_edit_hole', {}, 'quiz')}
      keywords={props.solution.answers}
      _multiple={props.hole._multiple || !!props.hole.choices}
      _errors={get(props, '_errors')}
      validating={props.validating}
      showCaseSensitive={true}
      random={props.hole.random}
      showScore={props.hasExpectedAnswers && props.hasScore}
      hasExpectedAnswers={props.hasExpectedAnswers}
      close={props.close}
      remove={props.remove}
      onChange={props.onChange}
      addKeyword={props.addKeyword}
      removeKeyword={props.removeKeyword}
      updateKeyword={props.updateKeyword}
    >
      <FormGroup
        id={`item-${props.hole.id}-size`}
        label={trans('size', {}, 'quiz')}
        warnOnly={!props.validating}
        error={get(props, '_errors.size')}
      >
        <input
          id={`item-${props.hole.id}-size`}
          type="number"
          min="0"
          value={props.hole.size}
          className="form-control"
          onChange={e => props.onChange('size', parseInt(e.target.value))}
        />
      </FormGroup>
    </KeywordsPopover>
  )
}

HolePopover.propTypes = {
  hole: T.shape({
    id: T.string.isRequired,
    _multiple: T.bool.isRequired,
    random: T.bool,
    size: T.number,
    choices: T.array
  }).isRequired,
  solution: T.shape({
    answers: T.array.isRequired
  }).isRequired,
  validating: T.bool,
  hasExpectedAnswers: T.bool.isRequired,
  hasScore: T.bool.isRequired,
  _errors: T.object,
  close: T.func.isRequired,
  remove:T.func.isRequired,
  onChange: T.func.isRequired, // update hole properties
  addKeyword: T.func.isRequired,
  removeKeyword: T.func.isRequired,
  updateKeyword: T.func.isRequired
}

class ClozeEditor extends Component {
  constructor(props) {
    super(props)

    // the TinyMce editor object. Filled when the editor is initialized and used to be able to insert holes
    // inside the text content
    this.editor = null
    this.state = {
      popoverHoleId: null,
      text: utils.setEditorHtml(this.props.item.text, this.props.item.holes, this.props.item.solutions, this.props.item.hasExpectedAnswers),
      dirty: false
    }

    this.addHole = this.addHole.bind(this)
    this.onHoleClick = this.onHoleClick.bind(this)
    this.onTextChange = this.onTextChange.bind(this)
  }

  componentDidUpdate(prevProps) {
    if (this.state.dirty) {
      if (
        get(prevProps, 'item.text') !== get(this.props, 'item.text') ||
        get(prevProps, 'item.holes') !== get(this.props, 'item.holes') ||
        get(prevProps, 'item.solutions') !== get(this.props, 'item.solutions')
      ) {
        let text = ''
        if (this.props.item.text) {
          text = utils.setEditorHtml(this.props.item.text, this.props.item.holes, this.props.item.solutions, this.props.item.hasExpectedAnswers)
        }

        this.setState({text: text, dirty: false})
      }
    }
  }

  onHoleClick(event) {
    const el = event.target

    if (el.classList.contains('edit-hole-btn') || el.classList.contains('edit-hole-btn-icon')) {
      const holeId = el.dataset.holeId
      const hole = getHoleFromId(this.props.item, holeId)

      if (hole) {
        this.setState({popoverHoleId: holeId})
      }
    } else if (el.classList.contains('delete-hole-btn') || el.classList.contains('delete-hole-btn-icon')) {
      const holeId = el.dataset.holeId

      // Remove from holes list
      const holes = cloneDeep(this.props.item.holes)
      const holeIndex = holes.findIndex(hole => hole.id === holeId)
      if (-1 < holeIndex) {
        holes.splice(holeIndex, 1)
      }

      // Remove from solutions
      const solutions = cloneDeep(this.props.item.solutions)
      const solutionsIndex = solutions.findIndex(solution => solution.holeId === holeId)
      if (-1 < solutionsIndex) {
        solutions.splice(solutionsIndex, 1)
      }

      // Remove from text
      const text = this.props.item.text.replace(`[[${holeId}]]`, '')

      this.props.update('text', text)
      this.props.update('holes', holes)
      this.props.update('solutions', solutions)

      this.setState({
        popoverHoleId: this.state.popoverHoleId === holeId ? null : this.state.popoverHoleId,
        dirty: true
      })
    }
  }

  addHole() {
    const hole = {
      id: makeId(),
      feedback: '',
      size: 10,
      _score: 0,
      _multiple: false,
      placeholder: ''
    }

    const keyword = keywordsUtils.createNew()
    keyword.text = ''
    if (this.editor.selection.getContent()) {
      // initialize first keyword with selected text
      keyword.text = this.editor.selection.getContent()
    }

    keyword._deletable = false

    const solution = {
      holeId: hole.id,
      answers: [keyword]
    }

    const holes = cloneDeep(this.props.item.holes)
    holes.push(hole)
    const solutions = cloneDeep(this.props.item.solutions)
    solutions.push(solution)

    const holeHtml = utils.makeTinyHtml(hole, solution, this.props.item.hasExpectedAnswers)
    this.editor.selection.setContent(' ' + holeHtml + ' ')
    const text = utils.getTextWithPlacerHoldersFromHtml(this.editor.getContent({format: 'html'}))

    this.props.update('holes', holes)
    this.props.update('solutions', solutions)
    this.props.update('text', text)

    this.setState({popoverHoleId: hole.id, dirty: true})
  }

  onTextChange(value) {
    this.setState({text: value, dirty: false})
    const newText = utils.getTextWithPlacerHoldersFromHtml(value)
    this.props.update('text', newText)

    const holesToRemove = []
    // we need to check if every hole is mapped to a placeholder
    // if there is no placeholder, then remove the hole
    this.props.item.holes.forEach(hole => {
      if (newText.indexOf(`[[${hole.id}]]`) < 0) {
        holesToRemove.push(hole.id)
      }
    })

    if (holesToRemove) {
      const holes = cloneDeep(this.props.item.holes)
      const solutions = cloneDeep(this.props.item.solutions)
      holesToRemove.forEach(toRemove => {
        holes.splice(holes.findIndex(hole => hole.id === toRemove), 1)
        solutions.splice(solutions.findIndex(solution => solution.holeId === toRemove), 1)
      })
      this.props.update('holes', holes)
      this.props.update('solutions', solutions)
    }

    if (this.state.popoverHoleId && holesToRemove.includes(this.state.popoverHoleId)) {
      this.setState({popoverHoleId: null})
    }
  }

  render() {
    const solutions = cloneDeep(this.props.item.solutions)
    solutions.forEach(solution => {
      solution.answers = solution.answers.map(a => Object.assign({}, a, {
        '_id': a._id ? a._id : makeId(),
        '_deletable': 1 < solution.answers.length
      }))
    })

    return (
      <FormGroup
        id={`cloze-text-${this.props.item.id}`}
        className="cloze-editor"
        label={trans('text')}
        required={true}
        help={trans('cloze_add_hole_help', {}, 'quiz')}
      >
        <HtmlInput
          id={`cloze-text-${this.props.item.id}`}
          className="component-container"
          value={this.state.text}
          onInit={(evt, editor) => this.editor = editor}
          config={{
            content_css: [
              theme('bootstrap'),
              theme('claroline-distribution-plugin-exo-quiz-resource')
            ]
          }}
          onChange={this.onTextChange}
          onClick={this.onHoleClick}
        />

        <Button
          type={CALLBACK_BUTTON}
          className="btn btn-outline-primary w-100"
          icon="fa fa-fw fa-plus"
          label={trans('create_cloze', {}, 'quiz')}
          callback={this.addHole}
        />

        {this.state.popoverHoleId && this.props.item.holes.find(hole => hole.id === this.state.popoverHoleId) &&
          <HolePopover
            hole={this.props.item.holes.find(hole => hole.id === this.state.popoverHoleId)}
            solution={solutions
              // get solutions for the current hole
              .find(solution => solution.holeId === this.state.popoverHoleId)
            }
            hasExpectedAnswers={this.props.item.hasExpectedAnswers}
            hasScore={this.props.hasAnswerScores}
            close={() => this.setState({popoverHoleId: null})}
            remove={() => {
              // Remove from holes list
              const holes = cloneDeep(this.props.item.holes)
              holes.splice(holes.findIndex(hole => hole.id === this.state.popoverHoleId), 1)

              // Remove from solutions
              //const solutions = cloneDeep(this.props.item.solutions)
              solutions.splice(solutions.findIndex(solution => solution.holeId === this.state.popoverHoleId), 1)

              // remove from text
              const text = this.props.item.text.replace(`[[${this.state.popoverHoleId}]]`, '')

              this.setState({popoverHoleId: null, dirty: true})
              this.props.update('holes', holes)
              this.props.update('solutions', solutions)
              this.props.update('text', text)
            }}
            onChange={(property, value) => {
              const newItem = cloneDeep(this.props.item)
              const hole = getHoleFromId(newItem, this.state.popoverHoleId)

              if (['size', '_multiple', 'random'].indexOf(property) > -1) {
                hole[property] = value
              } else {
                throw `${property} is not a valid hole attribute`
              }

              updateHoleChoices(hole, getSolutionFromHole(solutions, hole))
              this.props.update('holes', newItem.holes)
              this.props.update('solutions', solutions)
              this.setState({dirty: true})
            }}
            addKeyword={() => {
              const newItem = cloneDeep(this.props.item)
              const hole = getHoleFromId(newItem, this.state.popoverHoleId)
              const solution = getSolutionFromHole(solutions, hole)
              const keyword = keywordsUtils.createNew()
              keyword._deletable = solution.answers.length > 0

              solution.answers.push(keyword)

              updateHoleChoices(hole, solution)

              this.setState({dirty: true})
              this.props.update('holes', newItem.holes)
              this.props.update('solutions', solutions)
            }}
            removeKeyword={(keywordId) => {
              const newItem = cloneDeep(this.props.item)
              const hole = getHoleFromId(newItem, this.state.popoverHoleId)
              const solution = getSolutionFromHole(solutions, hole)
              const answers = solution.answers
              answers.splice(answers.findIndex(answer => answer._id === keywordId), 1)

              updateHoleChoices(hole, solution)

              answers.forEach(keyword => keyword._deletable = answers.length > 1)

              this.setState({dirty: true})
              this.props.update('holes', newItem.holes)
              this.props.update('solutions', solutions)
            }}
            updateKeyword={(keywordId, property, newValue) => {
              const newItem = cloneDeep(this.props.item)
              const hole = getHoleFromId(newItem, this.state.popoverHoleId)
              const solution = getSolutionFromHole(solutions, hole)
              const answer = solution.answers.find(answer => answer._id === keywordId)

              answer[property] = newValue
              updateHoleChoices(hole, solution)

              this.setState({dirty: true})
              this.props.update('holes', newItem.holes)
              this.props.update('solutions', solutions)
            }}
            validating={this.props.validating}
            _errors={get(this.props.item, '_errors.'+this.state.popoverHoleId)}
          />
        }
      </FormGroup>
    )
  }
}

implementPropTypes(ClozeEditor, ItemEditorTypes, {
  item: T.shape(ClozeItemTypes.propTypes).isRequired
})

export {
  ClozeEditor
}
