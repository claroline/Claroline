import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import cloneDeep from 'lodash/cloneDeep'

import {makeId} from '#/main/core/scaffolding/id'
import {keywords as keywordsUtils} from '#/plugin/exo/utils/keywords'
import {utils} from '#/plugin/exo/items/cloze/utils'
import {trans, tex} from '#/main/app/intl/translation'
import {Textarea} from '#/main/core/layout/form/components/field/textarea'
import {FormGroup} from '#/main/app/content/form/components/group'
import {ClozeItem as ClozeItemTypes} from '#/plugin/exo/items/grid/prop-types'
import {KeywordsPopover} from '#/plugin/exo/items/components/keywords.jsx'
import {FormData} from '#/main/app/content/form/containers/data'

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

function getSolutionFromHole(item, hole)
{
  return item.solutions.find(solution => solution.holeId === hole.id)
}

const HolePopover = props => {
  // Let's calculate the popover position
  // It will be positioned just under the edit button
  const btnElement = document.querySelector(`.cloze-hole[data-hole-id="${props.hole.id}"] .edit-hole-btn`)

  let left = btnElement.offsetLeft
  let top  = btnElement.offsetTop

  left += btnElement.offsetWidth / 2 // center popover and edit btn
  top  += btnElement.offsetHeight // position popover below edit btn

  left -= 180 // half size of the popover
  top  += 25 // take into account the form group label

  return (
    <KeywordsPopover
      id={props.hole.id}
      positionLeft={left}
      positionTop={top}
      title={tex('cloze_edit_hole')}
      keywords={props.solution.answers}
      _multiple={props.hole._multiple}
      _errors={get(props, '_errors')}
      validating={props.validating}
      showCaseSensitive={true}
      showScore={true}
      close={props.close}
      remove={props.remove}
      onChange={props.onChange}
      addKeyword={props.addKeyword}
      removeKeyword={props.removeKeyword}
      updateKeyword={props.updateKeyword}
    >
      <FormGroup
        id={`item-${props.hole.id}-size`}
        label={tex('size')}
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
    size: T.number
  }).isRequired,
  solution: T.shape({
    answers: T.array.isRequired
  }).isRequired,
  validating: T.bool.isRequired,
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
    this.selection = null
    this.word = null
    this.fnTextUpdate = () => {}
    this.state = {
      allowCloze: true,
      text: undefined
    }
    this.changeEditorMode = this.changeEditorMode.bind(this)
  }

  onSelect(word, cb) {
    this.word = word
    this.fnTextUpdate = cb
  }

  onHoleClick(el) {
    const newItem = cloneDeep(this.props.item)

    if (el.classList.contains('edit-hole-btn') || el.classList.contains('edit-hole-btn-icon')) {
      const hole = getHoleFromId(newItem, el.dataset.holeId)
      hole._multiple = !!hole.choices
      newItem._popover = true
      newItem._holeId = el.dataset.holeId
    } else if (el.classList.contains('delete-hole-btn') || el.classList.contains('delete-hole-btn-icon')) {
      const holes = newItem.holes
      const solutions = newItem.solutions

      // Remove from holes list
      holes.splice(holes.findIndex(hole => hole.id === this.props.item._holeId), 1)

      // Remove from solutions
      const solution = solutions.splice(solutions.findIndex(solution => solution.holeId === el.dataset.holeId), 1)

      let bestAnswer
      if (solution && 0 !== solution.length) {
        // Retrieve the best answer
        bestAnswer = utils.getBestAnswer(solution[0].answers)
      }

      // Replace hole with the best answer text
      const regex = new RegExp(`(\\[\\[${this.props.item._holeId}\\]\\])`, 'gi')
      newItem.text = newItem.text.replace(regex, bestAnswer ? bestAnswer.text : '')
      this.setState({text: utils.setEditorHtml(newItem.text, newItem.holes, newItem.solutions)})

      if (newItem._holeId && newItem._holeId === this.props.item._holeId) {
        newItem._popover = false
      }

    }

    this.props.update('holes', newItem.holes)
    this.props.update('_popover', newItem._popover)
    this.props.update('_holeId', newItem._holeId)
    this.props.update('text', newItem.text)
  }

  changeEditorMode(editorState) {
    this.setState({ allowCloze: editorState.minimal})
  }

  addHole(item) {
    const newItem = cloneDeep(item)

    const hole = {
      id: makeId(),
      feedback: '',
      size: 10,
      _score: 0,
      _multiple: false,
      placeholder: ''
    }

    const keyword = keywordsUtils.createNew()
    keyword.text = this.word
    keyword._deletable = false

    const solution = {
      holeId: hole.id,
      answers: [keyword]
    }

    newItem.holes.push(hole)
    newItem.solutions.push(solution)
    newItem._popover = true
    newItem._holeId = hole.id

    const text = this.fnTextUpdate(utils.makeTinyHtml(hole, solution))
    newItem.text = utils.getTextWithPlacerHoldersFromHtml(text)

    this.props.update('holes', newItem.holes)
    this.props.update('solutions', newItem.solutions)
    this.props.update('_popover', newItem._popover)
    this.props.update('_holeId', newItem._holeId)
    this.props.update('text', newItem.text)

    this.setState({text: text})
  }

  render() {
    if (this.props.item.text && undefined === this.state.text) {
      this.setState({text: utils.setEditorHtml(this.props.item.text, this.props.item.holes, this.props.item.solutions)})
    }

    return (<fieldset className="cloze-field">
      <FormGroup
        id={`cloze-text-${this.props.item.id}`}
        className="cloze-text"
        label={trans('text')}
        warnOnly={!this.props.validating}
        error={get(this.props.item, '_errors.text')}
      >
        <Textarea
          id={`cloze-text-${this.props.item.id}`}
          value={this.state.text}
          onChange={(value) => {
            //TODO: optimize this
            let item = Object.assign({}, this.props.item, {
              text: utils.getTextWithPlacerHoldersFromHtml(value)
            })

            const holesToRemove = []
            // we need to check if every hole is mapped to a placeholder
            // if there is not placeholder, then remove the hole
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
              item = Object.assign({}, this.props.item, {holes, solutions})
            }

            this.props.update('text', item.text)
            this.props.update('holes', item.holes)
            this.props.update('solutions', item.solutions)

            this.setState({text: value})
          }}
          onSelect={this.onSelect.bind(this)}
          onClick={this.onHoleClick.bind(this)}
          onChangeMode={this.changeEditorMode}
        />
      </FormGroup>

      <button
        type="button"
        className="btn btn-block btn-default"
        disabled={!this.state.allowCloze}
        onClick={() => this.addHole(this.props.item)}
      >
        <span className="fa fa-fw fa-plus" />
        {tex('create_cloze')}
      </button>

      {(this.props.item._popover && this.props.item._holeId) &&
        <HolePopover
          hole={this.props.item.holes.find(hole => hole.id === this.props.item._holeId)}
          solution={this.props.item.solutions.find(solution => solution.holeId === this.props.item._holeId)}
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

            if (newItem._holeId && newItem._holeId === this.props.item._holeId) {
              this.props.update('_popover', false)
            }

            this.props.update('text', newItem.text)
            this.props.update('holes', newItem.holes)

            this.setState({text: utils.setEditorHtml(newItem.text, newItem.holes, newItem.solutions)})
          }}
          onChange={(property, value) => {
            const newItem = cloneDeep(this.props.item)
            const hole = getHoleFromId(newItem, newItem._holeId)

            if (['size', '_multiple'].indexOf(property) > -1) {
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
    </fieldset>)
  }
}

export class ClozeEditor extends Component {


  render() {
    return (<FormData
      className="cloze-editor"
      embedded={true}
      name={this.props.formName}
      dataPart={this.props.path}
      sections={[
        {
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'clozeText',
              render: (item, errors) => <MainField {...this.props} item={item} errors={errors}/>
            }]
        }
      ]}
    />
    )}
}

ClozeEditor.propTypes = {
  item: T.shape({
    id: T.string.isRequired,
    text: T.string.isRequired,
    _errors: T.object,
    _popover: T.bool,
    _holeId: T.string,
    holes: T.arrayOf(T.shape({
      id: T.string.isRequired
    })).isRequired,
    solutions: T.arrayOf(T.shape({
      holeId: T.string.isRequired
    })).isRequired
  }),
  onChange: T.func.isRequired,
  validating: T.bool.isRequired
}
