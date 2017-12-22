import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import cloneDeep from 'lodash/cloneDeep'

import {tex} from '#/main/core/translation'

import {utils} from './utils/utils'
import {getOffsets} from './utils/selection'
import {getReactAnswerInputs} from './utils/selection-input.jsx'
import {SelectionText} from './utils/selection-text.jsx'

class SelectionPlayer extends Component {
  constructor(props) {
    super(props)

    //initialize the answers array
    if (!this.props.answer) {
      switch (this.props.item.mode) {
        case 'find': this.props.onChange({positions:[], tries:0, mode: 'find'}); break
        case 'select': this.props.onChange({mode: 'select', selections: []}); break
        case 'highlight': this.props.onChange({mode: 'highlight', highlights: []}); break
      }
    }
  }

  onFindAnswer(begin = null, addTry = null) {
    const answers = cloneDeep(this.props.answer)

    if (begin) {
      answers.positions.push(begin)
    }
    //maybe this should be stored in the server
    if (addTry) {
      answers.tries++
    }

    this.props.onChange(answers)
  }

  onHighlightAnswer(selectionId, colorId) {
    const answers = cloneDeep(this.props.answer)
    const highlights = answers.highlights
    const answer = highlights.find(highlight => highlight.selectionId === selectionId)
    answer ? answer.colorId = colorId: highlights.push({colorId,  selectionId})

    this.props.onChange(answers)
  }

  onSelectAnswer(selectionId, checked) {
    const answers = cloneDeep(this.props.answer)
    const selections = answers.selections

    if (checked) {
      selections.push(selectionId)
    } else {
      const index = selections.indexOf(selectionId)
      if (index > -1) selections.splice(index, 1)
    }

    this.props.onChange(answers)
  }

  getOnAnswer() {
    switch (this.props.item.mode) {
      case 'select': return this.onSelectAnswer.bind(this)
      case 'highlight': return this.onHighlightAnswer.bind(this)
    }
  }

  render() {
    const leftTries = (this.props.item.tries || 0) - (this.props.answer ? this.props.answer.tries: 0)

    return (
      <div>
        {this.props.item.mode === 'find' && leftTries > 0 &&
          <div className='select-tries'>
              <span className="btn btn-danger" style={{ cursor: 'default'}}>
                {tex('selection_missing_click')} <span className="badge">{this.props.item.penalty}</span>
              </span>
              {'\u00a0'}
              <span className="btn btn-primary" style={{ cursor: 'default'}}>
                {tex('left_tries')} <span className="badge">{leftTries}</span>
              </span>
          </div>
        }
        {this.props.item.mode === 'find' && leftTries <= 0 &&
          <div style={{textAlign:'center'}} className='selection-error'>{tex('no_try_left')}</div>
        }
        {this.props.item.mode !== 'find' &&
          <SelectionText
            className="panel-body"
            id={'selection-text-box-' + this.props.item.id}
            anchorPrefix="selection-element-yours"
            text={this.props.item.text}
            selections={getReactAnswerInputs(this.props.item, this.getOnAnswer(), this.answers)}
          />
        }
        {this.props.item.mode === 'find' &&
          <div id={'selection-text-box-' + this.props.item.id} className="pointer panel-body" dangerouslySetInnerHTML={{__html: utils.makeFindHtml(
            this.props.item.text,
            this.props.answer && this.props.answer.positions ?
              this.props.item.solutions.filter(solution => this.props.answer.positions.find(ans => ans >= solution.begin && ans <= solution.end)): []
            )}}
          />
        }
      </div>
    )
  }

  componentDidMount() {
    if (this.props.item.mode === 'find') {
      document.getElementById('selection-text-box-' + this.props.item.id).addEventListener(
        'click',
        () => {
          let offsets = getOffsets(document.getElementById('selection-text-box-' + this.props.item.id))
          if (offsets.trueStart !== offsets.trueEnd) {
            //must be a click and not a selection
            return
          }
          const leftTries = (this.props.item.tries || 0) - (this.props.answer ? this.props.answer.tries: 0)
          if (leftTries > 0) {
            this.props.item.solutions.forEach(element => {
              //remove the appended span size for style
              let toRemove = 0
              this.props.answer.positions.filter(position => position <= element.end).sort().forEach(() => toRemove += utils.getFindElementLength())

              const position = offsets.trueStart - toRemove
              if (position >= element.begin && position <= element.end) {
                this.onFindAnswer(position)
              }
            })
            this.onFindAnswer(null, true)
          }
        }
      )
    }
  }
}

SelectionPlayer.propTypes = {
  item: T.object,
  answer: T.object,
  onChange: T.func.isRequired
}

export {
  SelectionPlayer
}
