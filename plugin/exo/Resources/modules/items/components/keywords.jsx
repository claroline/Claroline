import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'
import classes from 'classnames'
import Popover from 'react-bootstrap/lib/Popover'

import {t, tex} from '#/main/core/translation'
import {Textarea} from '#/main/core/layout/form/components/field/textarea.jsx'
import {CheckGroup} from '#/main/core/layout/form/components/group/check-group.jsx'
import {ErrorBlock} from '#/main/core/layout/form/components/error-block.jsx'
import {TooltipElement} from '#/main/core/layout/components/tooltip-element.jsx'
import {TooltipButton} from '#/main/core/layout/button/components/tooltip-button.jsx'

/**
 * Edits a Keyword.
 *
 * @param props
 * @constructor
 */
class KeywordItem extends Component {
  constructor(props) {
    super(props)

    this.state = {
      showFeedback: false
    }
  }

  render() {
    return (
      <li className={
        classes(
          'keyword-item answer-item',
          {'expected-answer': this.props.showScore && this.props.keyword.score > 0 || this.props.keyword.expected },
          {'unexpected-answer': this.props.keyword.score <= 0 && !this.props.keyword.expected }
        )
      }>
        {!this.props.showScore &&
          <div className="keyword-expected">
            <TooltipElement
              id={`tooltip-${this.props.keyword._id}-keyword-expected`}
              tip={tex('grid_expected_keyword')}
            >
              <input
                id={`keyword-${this.props.keyword._id}-expected`}
                type="checkbox"
                checked={this.props.keyword.expected}
                onChange={e => this.props.updateKeyword('expected', e.target.checked)}
              />
            </TooltipElement>
          </div>
        }

        <div className="text-fields">
          <input
            type="text"
            id={`keyword-${this.props.keyword._id}-text`}
            title={tex('response')}
            value={this.props.keyword.text}
            className="form-control"
            placeholder={tex('keyword')}
            onChange={e => this.props.updateKeyword('text', e.target.value)}
          />

          {this.state.showFeedback &&
            <div className="feedback-container">
              <Textarea
                id={`keyword-${this.props.keyword._id}-feedback`}
                title={tex('feedback')}
                content={this.props.keyword.feedback}
                onChange={feedback => this.props.updateKeyword('feedback', feedback)}
              />
            </div>
          }
        </div>

        <div className="keyword-case-sensitive">
          <TooltipElement
            id={`tooltip-${this.props.keyword._id}-keyword-case-sensitive`}
            tip={tex('words_case_sensitive')}
          >
            <input
              id={`keyword-${this.props.keyword._id}-case-sensitive`}
              type="checkbox"
              disabled={!this.props.showCaseSensitive}
              title={tex('words_case_sensitive')}
              checked={this.props.keyword.caseSensitive}
              onChange={e => this.props.updateKeyword('caseSensitive', e.target.checked)}
            />
          </TooltipElement>
        </div>

        <div className="right-controls">
          {this.props.showScore &&
            <input
              id={`keyword-${this.props.keyword._id}-score`}
              title={tex('score')}
              type="number"
              className="form-control keyword-score"
              value={this.props.keyword.score}
              onChange={e => this.props.updateKeyword('score', parseFloat(e.target.value))}
            />
          }

          <TooltipButton
            id={`keyword-${this.props.keyword._id}-feedback-toggle`}
            className="btn-link-default"
            title={tex('words_feedback_info')}
            onClick={() => this.setState({showFeedback: !this.state.showFeedback})}
          >
            <span className="fa fa-fw fa-comments-o" />
          </TooltipButton>

          <TooltipButton
            id={`keyword-${this.props.keyword._id}-delete`}
            className="btn-link-default"
            disabled={!this.props.keyword._deletable}
            title={t('delete')}
            onClick={() => this.props.keyword._deletable && this.props.removeKeyword()}
          >
            <span className="fa fa-fw fa-trash-o" />
          </TooltipButton>
        </div>
      </li>
    )
  }
}

KeywordItem.propTypes = {
  keyword: T.shape({
    _id: T.string.isRequired,
    text: T.string.isRequired,
    feedback: T.string,
    score: T.number,
    expected: T.bool,
    caseSensitive: T.bool,
    _deletable: T.bool.isRequired
  }).isRequired,
  showCaseSensitive: T.bool.isRequired,
  showScore: T.bool.isRequired,
  updateKeyword: T.func.isRequired,
  removeKeyword: T.func.isRequired
}

KeywordItem.defaultProps = {
  expected: false,
  caseSensitive: false
}

/**
 * Edits a list of Keywords.
 *
 * @param props
 * @constructor
 */
const KeywordItems = props =>
  <div className="keyword-items">
    {get(props, '_errors.count') &&
      <ErrorBlock text={props._errors.count} warnOnly={!props.validating} />
    }
    {get(props, '_errors.noValidKeyword') &&
      <ErrorBlock text={props._errors.noValidKeyword} warnOnly={!props.validating} />
    }
    {get(props, '_errors.duplicate') &&
      <ErrorBlock text={props._errors.duplicate} warnOnly={!props.validating} />
    }
    {get(props, '_errors.text') &&
      <ErrorBlock text={props._errors.text} warnOnly={!props.validating} />
    }
    {get(props, '_errors.score') &&
      <ErrorBlock text={props._errors.score} warnOnly={!props.validating} />
    }

    <ul>
      {props.keywords.map(keyword =>
        <KeywordItem
          key={keyword._id}
          keyword={keyword}
          showCaseSensitive={props.showCaseSensitive}
          showScore={props.showScore}
          updateKeyword={(property, newValue) => props.updateKeyword(keyword._id, property, newValue)}
          removeKeyword={() => props.removeKeyword(keyword._id)}
        />
      )}
    </ul>

    <button
      type="button"
      className="add-keyword btn btn-block btn-default"
      onClick={props.addKeyword}
    >
      <span className="fa fa-fw fa-plus" />
      {tex('words_add_word')}
    </button>
  </div>

KeywordItems.propTypes = {
  /**
   * Enables case sensitiveness for keywords.
   */
  showCaseSensitive: T.bool,

  /**
   * Enables score for keywords.
   * Else it displays an "expected" checkbox.
   */
  showScore: T.bool,

  /**
   * The list of keywords to edit.
   */
  keywords: T.arrayOf(T.shape({
    _id: T.string.isRequired,
    score: T.number.isRequired,
    text: T.string.isRequired,
    feedback: T.string,
    caseSensitive: T.bool,
    _deletable: T.bool.isRequired
  })).isRequired,

  /**
   * Current validation state.
   */
  validating: T.bool.isRequired,

  /**
   * The list of validation error.
   */
  _errors: T.object,

  /**
   * Adds a new keyword in the collection.
   */
  addKeyword: T.func.isRequired,

  /**
   * Removes a keyword from the collection.
   *
   * @param {object} keyword
   */
  removeKeyword: T.func.isRequired,

  /**
   * Updates properties of a keyword.
   *
   * @param {object} keyword
   * @param {string} property
   * @param {mixed}  newValue
   */
  updateKeyword: T.func.isRequired
}

KeywordItems.defaultProps = {
  showCaseSensitive: false,
  showScore: false
}

/**
 * Displays a popover to create a solution based on keywords.
 * Used to configure an input with attached keywords (eg. Cloze, Grid, Words).
 *
 * @param props
 * @constructor
 */
const KeywordsPopover = props =>
  <Popover
    id={`keywords-popover-${props.id}`}
    className={classes('keywords-popover', props.className)}
    placement="bottom"
    positionLeft={props.positionLeft}
    positionTop={props.positionTop}
    style={props.style}
    title={
      <div>
        {props.title}

        <div className="popover-actions">
          {props.remove &&
            <TooltipButton
              id={`keywords-popover-${props.id}-remove`}
              title={tex('delete')}
              className="btn-link-default"
              onClick={props.remove}
            >
              <span className="fa fa-fw fa-trash-o" />
            </TooltipButton>
          }

          <TooltipButton
            id={`keywords-popover-${props.id}-close`}
            title={tex('close')}
            className="btn-link-default"
            disabled={!isEmpty(props._errors)}
            onClick={props.close}
          >
            <span className="fa fa-fw fa-times" />
          </TooltipButton>
        </div>
      </div>
    }
  >
    {props.children}

    <CheckGroup
      checkId={`keywords-show-${props.id}-list`}
      label={tex('submit_a_list')}
      checked={props._multiple}
      onChange={checked => props.onChange('_multiple', checked)}
    />

    <KeywordItems
      keywords={props.keywords}
      validating={props.validating}
      _errors={get(props, '_errors.keywords')}
      showCaseSensitive={props.showCaseSensitive}
      showScore={props.showScore}
      addKeyword={props.addKeyword}
      updateKeyword={props.updateKeyword}
      removeKeyword={props.removeKeyword}
    />
  </Popover>

KeywordsPopover.propTypes = {
  /**
   * An unique identifier to create HTML anchors.
   */
  id: T.string.isRequired,

  /**
   * The title of the popover.
   */
  title: T.string.isRequired,

  /**
   * Additional classes to append to popover.
   */
  className: T.string,

  /**
   * Custom position left.
   */
  positionLeft: T.number,

  /**
   * Custom position top.
   */
  positionTop: T.number,

  /**
   * If true, the keywords will be proposed to the user in a list.
   * If false, the user will have to type his answer in a text input.
   */
  _multiple: T.bool.isRequired,

  /**
   * The collection of keywords for the solution
   */
  keywords: T.array.isRequired,

  /**
   * Current validation state.
   */
  validating: T.bool.isRequired,

  /**
   * The list of validation error.
   */
  _errors: T.object,

  /**
   * Enables keyword case sensitiveness.
   */
  showCaseSensitive: T.bool.isRequired,

  /**
   * Show score for each keyword.
   */
  showScore: T.bool.isRequired,

  /**
   * Custom fields
   */
  children: T.node,

  /**
   * Custom css rules.
   */
  style: T.object,

  /**
   * Removes the current solution.
   */
  remove: T.func,

  /**
   * Closes the popover form.
   */
  close: T.func.isRequired,

  /**
   * Handles changes in solution properties.
   *
   * @param {string} properties
   * @param {mixed}  newValue
   */
  onChange: T.func.isRequired,

  /**
   * Adds a new keyword to the solution.
   */
  addKeyword: T.func.isRequired,

  /**
   * Removes a keyword from the solution.
   *
   * @param {object} keyword
   */
  removeKeyword: T.func.isRequired,

  /**
   * Updates properties of a keyword.
   *
   * @param {object} keyword
   * @param {string} property
   * @param {mixed}  newValue
   */
  updateKeyword: T.func.isRequired
}

export {
  KeywordItems,
  KeywordsPopover
}
