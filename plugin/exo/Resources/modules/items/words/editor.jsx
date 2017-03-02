import React, {Component, PropTypes as T} from 'react'
import classes from 'classnames'
import get from 'lodash/get'
import {t, tex} from './../../utils/translate'
import {Textarea} from './../../components/form/textarea.jsx'
import {CheckGroup} from './../../components/form/check-group.jsx'
import {TooltipButton} from './../../components/form/tooltip-button.jsx'
import {actions} from './editor.js'

class WordItem extends Component {
  constructor(props) {
    super(props)
    this.state = {showFeedback: false}
  }

  render() {
    return (
      <div className={
        classes(
          'word-item',
          {'positive-score': this.props.score > 0 },
          {'negative-score': this.props.score <= 0 }
        )
      }>
        <div className="text-fields">
          <input
            type="text"
            id={`solution-${this.props.index}-text`}
            title={tex('response')}
            value={this.props.text}
            className="form-control"
            onChange={e => this.props.onChange(
              actions.updateSolution(this.props.index, 'text', e.target.value)
            )}
          />
          {this.state.showFeedback &&
            <div className="feedback-container">
              <Textarea
                id={`solution-${this.props.index}-feedback`}
                title={tex('feedback')}
                content={this.props.feedback}
                onChange={feedback => this.props.onChange(
                  actions.updateSolution(this.props.index, 'feedback', feedback)
                )}
              />
            </div>
          }
        </div>
        <div className="word-case-sensitive">
          <input
            disabled={!this.props.showCaseSensitive}
            type="checkbox"
            title={tex('words_case_sensitive')}
            checked={this.props.caseSensitive}
            readOnly={!this.props.showCaseSensitive}
            onChange={e => this.props.onChange(
              actions.updateSolution(this.props.index, 'caseSensitive', e.target.checked)
            )}
          />
        </div>
        <div className="right-controls">
          <input
            id={`solution-${this.props.index}-score`}
            title={tex('score')}
            type="number"
            className="form-control word-score"
            value={this.props.score}
            onChange={e => this.props.onChange(
              actions.updateSolution(this.props.index, 'score', e.target.value)
            )}
          />

          <TooltipButton
            id={`words-${this.props.index}-feedback-toggle`}
            className="fa fa-comments-o"
            title={tex('words_feedback_info')}
            onClick={() => this.setState({showFeedback: !this.state.showFeedback})}
          />
          <TooltipButton
            id={`words-${this.props.index}-delete`}
            className="fa fa-trash-o"
            enabled={this.props.deletable}
            title={t('delete')}
            onClick={() => this.props.deletable && this.props.onChange(
              actions.removeSolution(this.props.index)
            )}
          />
        </div>
      </div>
    )
  }
}

WordItem.propTypes = {
  text: T.string.isRequired,
  feedback: T.string.isRequired,
  score: T.number.isRequired,
  caseSensitive: T.bool.isRequired,
  deletable: T.bool.isRequired,
  index: T.number.isRequired,
  showCaseSensitive: T.bool.isRequired,
  onChange: T.func.isRequired
}

const WordsItems = props =>
  <div>
    {get(props.item, '_errors.solutions') &&
      <div className="error-text">
        <span className="fa fa-warning"></span>
        {props.item._errors.solutions}
      </div>
    }
    <ul className="words-items">
      {props.item.solutions.map((solution, index) =>
        <li key={index}>
          <WordItem
            index={index}
            text={solution.text}
            score={solution.score}
            feedback={solution.feedback}
            caseSensitive={props.item._wordsCaseSensitive && solution.caseSensitive}
            showCaseSensitive={props.item._wordsCaseSensitive}
            deletable={solution._deletable}
            onChange={props.onChange}
          />
        </li>
      )}
    </ul>
    <div className="footer text-center">
      <button
        id="add-word-button"
        type="button"
        className="btn btn-default"
        onClick={() => props.onChange(actions.addSolution())}
      >
        <span className="fa fa-plus"/>
        {tex('words_add_word')}
      </button>
    </div>
  </div>

WordsItems.propTypes = {
  item: T.shape({
    solutions: T.arrayOf(T.shape({
      score: T.number.isRequired,
      text: T.string.isRequired,
      feedback: T.string,
      caseSensitive: T.bool.isRequired,
      _deletable: T.bool.isRequired
    })).isRequired,
    _errors: T.object,
    _wordsCaseSensitive: T.bool.isRequired
  }).isRequired,
  onChange: T.func.isRequired
}

export const Words = props =>
  <fieldset>
    <CheckGroup
      checkId={`item-${props.item.id}-_wordsCaseSensitive`}
      checked={props.item._wordsCaseSensitive}
      label={tex('words_show_case_sensitive_option')}
      onChange={checked => props.onChange(actions.updateProperty('_wordsCaseSensitive', checked))}
    />
    <WordsItems {...props}/>
  </fieldset>

Words.propTypes = {
  item: T.shape({
    id: T.string.isRequired,
    _wordsCaseSensitive: T.bool.isRequired,
    solutions: T.arrayOf(T.object).isRequired
  }).isRequired,
  onChange: T.func.isRequired
}
