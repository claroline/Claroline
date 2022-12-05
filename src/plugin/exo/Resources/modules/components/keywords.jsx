import React, {Component, Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'
import classes from 'classnames'
import Popover from 'react-bootstrap/lib/Popover'

import {trans} from '#/main/app/intl/translation'
import {HtmlInput} from '#/main/app/data/types/html/components/input'
import {CheckGroup} from '#/main/core/layout/form/components/group/check-group'
import {DataError} from '#/main/app/data/components/error'
import {TooltipOverlay} from '#/main/app/overlays/tooltip/components/overlay'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {DateInput} from '#/main/app/data/types/date/components/input'

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
      <li className={classes('keyword-item answer-item', this.props.hasExpectedAnswers && {
        'expected-answer': this.props.keyword.score > 0,
        'unexpected-answer': this.props.keyword.score <= 0
      })}>
        {this.props.hasExpectedAnswers && !this.props.showScore &&
          <div className="keyword-expected">
            <TooltipOverlay
              id={`tooltip-${this.props.keyword._id}-keyword-expected`}
              tip={trans('grid_expected_keyword', {}, 'quiz')}
            >
              <input
                id={`keyword-${this.props.keyword._id}-expected`}
                type="checkbox"
                checked={this.props.keyword.score > 0}
                onChange={e => this.props.updateKeyword('score', e.target.checked ? 1 : 0)}
              />
            </TooltipOverlay>
          </div>
        }

        <div className="text-fields">
          {'date' === this.props.contentType ?
            <DateInput
              id={`keyword-${this.props.keyword._id}-text`}
              placeholder={trans('keyword', {number: this.props.index + 1}, 'quiz')}
              value={this.props.keyword.text}
              onChange={value => this.props.updateKeyword('text', value)}
            />
            :
            <input
              type="text"
              id={`keyword-${this.props.keyword._id}-text`}
              title={trans('keyword', {}, 'quiz')}
              value={this.props.keyword.text}
              className="form-control"
              placeholder={trans('keyword', {number: this.props.index + 1}, 'quiz')}
              onChange={e => this.props.updateKeyword('text', e.target.value)}
            />
          }

          {this.state.showFeedback &&
            <HtmlInput
              id={`keyword-${this.props.keyword._id}-feedback`}
              className="feedback-control"
              value={this.props.keyword.feedback}
              onChange={feedback => this.props.updateKeyword('feedback', feedback)}
            />
          }
        </div>

        {'text' === this.props.contentType &&
          <div className="keyword-case-sensitive">
            <TooltipOverlay
              id={`tooltip-${this.props.keyword._id}-keyword-case-sensitive`}
              tip={trans('words_case_sensitive', {}, 'quiz')}
            >
              <input
                id={`keyword-${this.props.keyword._id}-case-sensitive`}
                type="checkbox"
                disabled={!this.props.showCaseSensitive}
                title={trans('words_case_sensitive', {}, 'quiz')}
                checked={this.props.keyword.caseSensitive}
                onChange={e => this.props.updateKeyword('caseSensitive', e.target.checked)}
              />
            </TooltipOverlay>
          </div>
        }

        <div className="right-controls">
          {this.props.showScore &&
            <input
              id={`keyword-${this.props.keyword._id}-score`}
              title={trans('score', {}, 'quiz')}
              type="number"
              className="form-control score keyword-score"
              value={this.props.keyword.score}
              onChange={e => this.props.updateKeyword('score', parseFloat(e.target.value))}
            />
          }

          <Button
            id={`keyword-${this.props.keyword._id}-feedback-toggle`}
            className="btn-link"
            type={CALLBACK_BUTTON}
            icon="fa fa-fw fa-comments"
            label={trans('words_feedback_info', {}, 'quiz')}
            callback={() => this.setState({showFeedback: !this.state.showFeedback})}
            tooltip="top"
          />

          <Button
            id={`keyword-${this.props.keyword._id}-delete`}
            className="btn-link"
            type={CALLBACK_BUTTON}
            icon="fa fa-fw fa-trash"
            label={trans('delete', {}, 'actions')}
            callback={() => this.props.keyword._deletable && this.props.removeKeyword()}
            disabled={!this.props.keyword._deletable}
            tooltip="top"
            dangerous={true}
          />
        </div>
      </li>
    )
  }
}

KeywordItem.propTypes = {
  index: T.number.isRequired,
  keyword: T.shape({
    _id: T.string.isRequired,
    text: T.string.isRequired,
    feedback: T.string,
    score: T.number,
    expected: T.bool,
    caseSensitive: T.bool,
    _deletable: T.bool.isRequired
  }).isRequired,
  /**
   * The simplified content type of the keyword (eg. text, date).
   */
  contentType: T.string.isRequired,
  showCaseSensitive: T.bool.isRequired,
  showScore: T.bool.isRequired,
  hasExpectedAnswers: T.bool.isRequired,
  updateKeyword: T.func.isRequired,
  removeKeyword: T.func.isRequired
}

KeywordItem.defaultProps = {
  expected: false,
  caseSensitive: false,
  hasExpectedAnswers: true
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
      <DataError error={props._errors.count} warnOnly={!props.validating} />
    }
    {get(props, '_errors.noValidKeyword') &&
      <DataError error={props._errors.noValidKeyword} warnOnly={!props.validating} />
    }
    {get(props, '_errors.duplicate') &&
      <DataError error={props._errors.duplicate} warnOnly={!props.validating} />
    }
    {get(props, '_errors.text') &&
      <DataError error={props._errors.text} warnOnly={!props.validating} />
    }
    {get(props, '_errors.score') &&
      <DataError error={props._errors.score} warnOnly={!props.validating} />
    }

    <ul>
      {props.keywords.map((keyword, index) =>
        <KeywordItem
          key={keyword._id}
          index={index}
          keyword={keyword}
          contentType={props.contentType}
          showCaseSensitive={props.showCaseSensitive}
          showScore={props.showScore}
          hasExpectedAnswers={props.hasExpectedAnswers}
          updateKeyword={(property, newValue) => props.updateKeyword(keyword._id, property, newValue)}
          removeKeyword={() => props.removeKeyword(keyword._id)}
        />
      )}
    </ul>

    <Button
      type={CALLBACK_BUTTON}
      className="add-keyword btn btn-block"
      icon="fa fa-fw fa-plus"
      label={trans('words_add_word', {}, 'quiz')}
      callback={props.addKeyword}
    />
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
   * Enables definition of a correct keyword.
   */
  hasExpectedAnswers: T.bool,

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
   * The simplified content type of the keyword (eg. text, date).
   */
  contentType: T.string,

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
   * @param {*}      newValue
   */
  updateKeyword: T.func.isRequired
}

KeywordItems.defaultProps = {
  showCaseSensitive: false,
  showScore: false,
  hasExpectedAnswers: true
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
      <Fragment>
        {props.title}

        <div className="popover-actions">
          {props.remove &&
            <Button
              id={`keywords-popover-${props.id}-remove`}
              className="btn-link"
              type={CALLBACK_BUTTON}
              icon="fa fa-fw fa-trash"
              label={trans('delete', {}, 'actions')}
              callback={props.remove}
              tooltip="top"
            />
          }

          <Button
            id={`keywords-popover-${props.id}-close`}
            className="btn-link"
            type={CALLBACK_BUTTON}
            icon="fa fa-fw fa-times"
            label={trans('close', {}, 'actions')}
            disabled={!isEmpty(props._errors)}
            callback={props.close}
            tooltip="top"
          />
        </div>
      </Fragment>
    }
  >
    {props.children}

    <CheckGroup
      id={`keywords-show-${props.id}-list`}
      label={trans('submit_a_list', {}, 'quiz')}
      value={props._multiple}
      onChange={checked => props.onChange('_multiple', checked)}
    />

    {props._multiple &&
      <div className="sub-fields">
        <CheckGroup
          id={`keywords-${props.id}-shuffle`}
          label={trans('shuffle_answers', {}, 'quiz')}
          value={props.random}
          onChange={checked => props.onChange('random', checked)}
        />
      </div>
    }

    <KeywordItems
      keywords={props.keywords}
      validating={props.validating}
      _errors={get(props, '_errors.keywords')}
      showCaseSensitive={props.showCaseSensitive}
      contentType={props.contentType}
      showScore={props.showScore}
      hasExpectedAnswers={props.hasExpectedAnswers}
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

  random: T.bool,

  /**
   * The collection of keywords for the solution
   */
  keywords: T.array.isRequired,

  /**
   * The simplified content type of the keyword (eg. text, date).
   */
  contentType: T.string.isRequired,

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
   * Define if there are expected answers.
   */
  hasExpectedAnswers: T.bool.isRequired,

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
   * @param {*}      newValue
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
   * @param {*}      newValue
   */
  updateKeyword: T.func.isRequired
}

KeywordsPopover.defaultProps = {
  hasExpectedAnswers: true,
  contentType: 'text'
}

export {
  KeywordItems,
  KeywordsPopover
}
