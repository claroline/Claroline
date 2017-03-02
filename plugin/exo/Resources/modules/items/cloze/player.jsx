import React, {Component, PropTypes as T} from 'react'
import {utils} from './utils/utils'
import cloneDeep from 'lodash/cloneDeep'
import {tex} from './../../utils/translate'

export class ClozePlayer extends Component {
  constructor(props) {
    super(props)
    this.onAnswer = this.onAnswer.bind(this)
    this.elements = utils.split(this.props.item.text, this.props.item.holes, this.props.item.solutions)
  }

  onAnswer(holeId, text) {
    let answer = null
    const answers = cloneDeep(this.props.answer)
    if (this.props.answer.length > 0) answer = answers.find(item => item.holeId === holeId)

    ;(answer) ?
      answer.answerText = text:
      answers.push({answerText: text, holeId})

    return answers
  }

  getHtml() {
    return this.elements.map((el, key) => {
      return el.choices ?
        `
        ${el.text}
        <select
          id=${'answer'+key}
          defaultValue=''
          class="form-control inline-select"
        >
          <option value=''>${tex('please_choose')}</option>
          ${el.choices.map((choice, idx) => `<option value=${choice} key=${idx}>${ choice }</option>`)}
        </select>
      `:
      el.holeId ?
       `
         ${el.text}
         <input
           id=${'answer'+key}
           class="form-control inline-select"
           type="text"
           size=${el.size}
         />
     `:
      el.text
    }).reduce((a, b) => a += b)
  }

  render() {
    return <div dangerouslySetInnerHTML={{__html: this.getHtml()}} />
  }

  componentDidMount() {
    this.elements.forEach((el, key) => {
      let htmlElement = document.getElementById('answer' + key)
      if (htmlElement) {
        htmlElement.addEventListener(
          'change',
          e => this.props.onChange(this.onAnswer(el.holeId, e.target.value))
        )
      }
    })
  }
}


ClozePlayer.propTypes = {
  item: T.shape({
    id: T.string.isRequired,
    holes: T.array.isRequired,
    solutions: T.array.isRequired,
    text: T.string.isRequired
  }).isRequired,
  answer: T.array,
  onChange: T.func.isRequired
}

ClozePlayer.defaultProps = {
  answer: []
}
