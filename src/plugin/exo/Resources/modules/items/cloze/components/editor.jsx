import React, {Component} from 'react'
import get from 'lodash/get'
import cloneDeep from 'lodash/cloneDeep'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {trans} from '#/main/app/intl/translation'
import {FormData} from '#/main/app/content/form/containers/data'
import {FormGroup} from '#/main/app/content/form/components/group'
import {HtmlInput} from '#/main/app/data/types/html/components/input'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

import {makeId} from '#/main/core/scaffolding/id'

import {ItemEditor as ItemEditorType} from '#/plugin/exo/items/prop-types'
import {ClozeItem as ClozeItemType} from '#/plugin/exo/items/cloze/prop-types'
import {KeywordsPopover} from '#/plugin/exo/components/keywords'
import {keywords as keywordsUtils} from '#/plugin/exo/utils/keywords'
import {utils} from '#/plugin/exo/items/cloze/utils'
import {theme} from '#/main/theme/config'

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

function getSolutionFromHole(item, hole) {
  return item.solutions.find(solution => solution.holeId === hole.id)
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
      _multiple={props.hole._multiple}
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
    size: T.number
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

class MainField extends Component {
  constructor(props) {
    super(props)
    //this.selection = null
    //this.word = null
    //this.fnTextUpdate = () => {}

    // the TinyMce editor object. Filled when the editor is initialized and used to be able to insert holes
    // inside the text content
    this.editor = null

    let text = ''
    if (this.props.item.text) {
      text = utils.setEditorHtml(this.props.item.text, this.props.item.holes, this.props.item.solutions, this.props.item.hasExpectedAnswers)
    }

    this.state = {
      allowCloze: true,
      text: text,
      selection: null
    }

    this.addHole = this.addHole.bind(this)
    this.changeEditorMode = this.changeEditorMode.bind(this)
    this.onHoleClick = this.onHoleClick.bind(this)
  }

  componentDidUpdate(prevProps, prevState, snapshot) {
    if (get(prevProps, 'item.text') !== get(this.props, 'item.text')) {
      let text = ''
      if (this.props.item.text) {
        text = utils.setEditorHtml(this.props.item.text, this.props.item.holes, this.props.item.solutions, this.props.item.hasExpectedAnswers)
      }

      this.setState({text: text})
    }
  }

  changeEditorMode(editorState) {
    this.setState({allowCloze: editorState.minimal})
  }

  onHoleClick(event) {
    const el = event.target
    const newItem = cloneDeep(this.props.item)

    let needUpdate = false
    if (el.classList.contains('edit-hole-btn') || el.classList.contains('edit-hole-btn-icon')) {
      const hole = getHoleFromId(newItem, el.dataset.holeId)
      hole._multiple = !!hole.choices
      newItem._popover = true
      newItem._holeId = el.dataset.holeId

      needUpdate = true
    } else if (el.classList.contains('delete-hole-btn') || el.classList.contains('delete-hole-btn-icon')) {
      const holeId = el.dataset.holeId
      const holes = newItem.holes
      const solutions = newItem.solutions

      // Remove from holes list
      const holeIndex = holes.findIndex(hole => hole.id === holeId)

      if (-1 < holeIndex) {
        holes.splice(holeIndex, 1)
      }

      // Remove from solutions
      const solutionsIndex = solutions.findIndex(solution => solution.holeId === holeId)
      let solution

      if (-1 < solutionsIndex) {
        solution = solutions.splice(solutionsIndex, 1)
      }

      let bestAnswer
      if (solution && 0 !== solution.length) {
        // Retrieve the best answer
        bestAnswer = utils.getBestAnswer(solution[0].answers)
      }

      // Replace hole with the best answer text
      const regex = new RegExp(`(\\[\\[${holeId}\\]\\])`, 'gi')
      newItem.text = newItem.text.replace(regex, bestAnswer ? bestAnswer.text : '')
      this.setState({text: utils.setEditorHtml(newItem.text, newItem.holes, newItem.solutions, newItem.hasExpectedAnswers)})

      if (newItem._holeId && newItem._holeId === holeId) {
        newItem._popover = false
      }

      needUpdate = true
    }

    if (needUpdate) {
      this.props.update('holes', newItem.holes)
      this.props.update('solutions', newItem.solutions)
      this.props.update('_popover', newItem._popover || false)
      this.props.update('_holeId', newItem._holeId || null)
      this.props.update('text', newItem.text)
    }
  }

  addHole() {
    const newItem = cloneDeep(this.props.item)

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

    newItem.holes.push(hole)
    newItem.solutions.push(solution)
    newItem._popover = true
    newItem._holeId = hole.id

    let text = this.state.text || ''

    const holeHtml = utils.makeTinyHtml(hole, solution, newItem.hasExpectedAnswers)
    if (this.editor.selection.getContent()) {
      this.editor.selection.setContent(holeHtml)
      text = this.editor.getContent({format: 'html'})
    } else {
      // extra space are added to be able to add content before/after the hole
      text += (this.state.text && 0 > this.state.text ? ' ' : '')+holeHtml+ ' '
    }

    newItem.text = utils.getTextWithPlacerHoldersFromHtml(text)

    this.props.update('holes', newItem.holes)
    this.props.update('solutions', newItem.solutions)
    this.props.update('_popover', newItem._popover)
    this.props.update('_holeId', newItem._holeId)
    this.props.update('text', newItem.text)
  }

  render() {
    return (
      <fieldset className="cloze-field">
        <HtmlInput
          id={`cloze-text-${this.props.item.id}`}
          className="component-container"
          value={this.state.text}
          onInit={(evt, editor) => {
            this.editor = editor
          }}
          config={{
            content_css: [
              theme('bootstrap'),
              theme('claroline-distribution-plugin-exo-quiz-resource')
            ]
          }}
          onChange={(value) => {
            let item = Object.assign({}, this.props.item, {
              text: utils.getTextWithPlacerHoldersFromHtml(value)
            })

            const holesToRemove = []
            // we need to check if every hole is mapped to a placeholder
            // if there is no placeholder, then remove the hole
            this.props.item.holes.forEach(hole => {
              if (item.text.indexOf(`[[${hole.id}]]`) < 0) {
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
              item = Object.assign({}, item, {holes, solutions})
            }

            if (this.props.item._popover && holesToRemove.includes(this.props.item._holeId)) {
              this.props.update('_popover', false)
              this.props.update('_holeId', null)
            }

            this.props.update('text', item.text)
            this.props.update('holes', item.holes)
            this.props.update('solutions', item.solutions)
          }}
          onClick={this.onHoleClick}
        />

        <Button
          type={CALLBACK_BUTTON}
          className="btn btn-block"
          icon="fa fa-fw fa-plus"
          label={trans('create_cloze', {}, 'quiz')}
          disabled={!this.state.allowCloze}
          callback={this.addHole}
        />

        {(this.props.item._popover && this.props.item._holeId) &&
          <HolePopover
            hole={this.props.item.holes.find(hole => hole.id === this.props.item._holeId)}
            solution={this.props.item.solutions.find(solution => solution.holeId === this.props.item._holeId)}
            hasExpectedAnswers={this.props.item.hasExpectedAnswers}
            hasScore={this.props.hasScore}
            close={() => this.props.update('_popover', false)}
            remove={() => {
              const newItem = cloneDeep(this.props.item)
              const holes = newItem.holes
              const solutions = newItem.solutions

              // Remove from holes list
              holes.splice(holes.findIndex(hole => hole.id === this.props.item._holeId), 1)

              // Remove from solutions
              const solution = solutions.splice(solutions.findIndex(solution => solution.holeId === this.props.item._holeId), 1)

              let bestAnswer
              if (solution && 0 !== solution.length) {
                // Retrieve the best answer
                bestAnswer = utils.getBestAnswer(solution[0].answers)
              }

              // Replace hole with the best answer text
              const regex = new RegExp(`(\\[\\[${this.props.item._holeId}\\]\\])`, 'gi')
              newItem.text = newItem.text.replace(regex, bestAnswer ? bestAnswer.text : '')

              this.props.update('_popover', false)
              this.props.update('_holeId', null)
              this.props.update('text', newItem.text)
              this.props.update('holes', newItem.holes)
              this.props.update('solutions', newItem.solutions)
            }}
            onChange={(property, value) => {
              const newItem = cloneDeep(this.props.item)
              const hole = getHoleFromId(newItem, newItem._holeId)

              if (['size', '_multiple', 'random'].indexOf(property) > -1) {
                hole[property] = value
              } else {
                throw `${property} is not a valid hole attribute`
              }

              updateHoleChoices(hole, getSolutionFromHole(newItem, hole))
              this.props.update('holes', newItem.holes)
              this.props.update('solutions', newItem.solutions)
            }}


            addKeyword={() => {
              const newItem = cloneDeep(this.props.item)
              const hole = getHoleFromId(newItem, this.props.item._holeId)
              const solution = getSolutionFromHole(newItem, hole)
              const keyword = keywordsUtils.createNew()
              keyword._deletable = solution.answers.length > 0

              solution.answers.push(keyword)

              updateHoleChoices(hole, solution)
              this.props.update('holes', newItem.holes)
              this.props.update('solutions', newItem.solutions)
            }}
            removeKeyword={(keywordId) => {
              const newItem = cloneDeep(this.props.item)
              const hole = getHoleFromId(newItem, this.props.item._holeId)
              const solution = getSolutionFromHole(newItem, hole)
              const answers = solution.answers
              answers.splice(answers.findIndex(answer => answer._id === keywordId), 1)

              updateHoleChoices(hole, solution)

              answers.forEach(keyword => keyword._deletable = answers.length > 1)

              this.props.update('holes', newItem.holes)
              this.props.update('solutions', newItem.solutions)
            }}
            updateKeyword={(keywordId, property, newValue) => {
              const newItem = cloneDeep(this.props.item)
              const hole = getHoleFromId(newItem, this.props.item._holeId)
              const solution = getSolutionFromHole(newItem, hole)
              const answer = solution.answers.find(answer => answer._id === keywordId)

              answer[property] = newValue
              updateHoleChoices(hole, solution)

              this.props.update('holes', newItem.holes)
              this.props.update('solutions', newItem.solutions)
            }}
            validating={this.props.validating}
            _errors={get(this.props.item, '_errors.'+this.props.item._holeId)}
          />
        }
      </fieldset>
    )
  }
}

const ClozeEditor = props => {
  const newItem = cloneDeep(props.item)
  newItem.solutions.forEach(solution => {
    solution.answers = solution.answers.map(a => Object.assign({}, a, {
      '_id': a._id ? a._id : makeId(),
      '_deletable': 1 < solution.answers.length
    }))
  })

  const ClozeText = (
    <MainField
      {...props}
      item={newItem}
      hasScore={props.hasAnswerScores}
    />
  )

  return (
    <FormData
      className="cloze-editor"
      embedded={true}
      name={props.formName}
      dataPart={props.path}
      definition={[
        {
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'clozeText',
              label: trans('text'),
              required: true,
              component: ClozeText,
              help: trans('cloze_add_hole_help', {}, 'quiz')
            }
          ]
        }
      ]}
    />
  )
}

implementPropTypes(ClozeEditor, ItemEditorType, {
  item: T.shape(ClozeItemType.propTypes).isRequired
})

export {
  ClozeEditor
}
