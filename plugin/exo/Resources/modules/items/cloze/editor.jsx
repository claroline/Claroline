import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {t, tex} from '#/main/core/translation'
import {Textarea} from '#/main/core/layout/form/components/field/textarea.jsx'
import {FormGroup} from '#/main/core/layout/form/components/group/form-group.jsx'
import {actions} from './editor'

import {KeywordsPopover} from './../components/keywords.jsx'

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
        controlId={`item-${props.hole.id}-size`}
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
      this.props.onChange(actions.openHole(el.dataset.holeId))
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

        <button
          type="button"
          className="btn btn-block btn-default"
          disabled={!this.state.allowCloze}
          onClick={() => this.props.onChange(
            this.addHole()
          )}
        >
          <span className="fa fa-fw fa-plus" />
          {tex('create_cloze')}
        </button>

        {(this.props.item._popover && this.props.item._holeId) &&
          <HolePopover
            hole={this.props.item.holes.find(hole => hole.id === this.props.item._holeId)}
            solution={this.props.item.solutions.find(solution => solution.holeId === this.props.item._holeId)}
            close={() => this.props.onChange(
              actions.closePopover()
            )}
            remove={() => this.props.onChange(
              actions.removeHole(this.props.item._holeId)
            )}
            onChange={(property, value) => this.props.onChange(
              actions.updateHole(this.props.item._holeId, property, value)
            )}
            addKeyword={() => this.props.onChange(
              actions.addAnswer(this.props.item._holeId)
            )}
            removeKeyword={(keywordId) => this.props.onChange(
              actions.removeAnswer(this.props.item._holeId, keywordId)
            )}
            updateKeyword={(keywordId, property, newValue) => this.props.onChange(
              actions.updateAnswer(this.props.item._holeId, keywordId, property, newValue)
            )}
            validating={this.props.validating}
            _errors={get(this.props.item, '_errors.'+this.props.item._holeId)}
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
