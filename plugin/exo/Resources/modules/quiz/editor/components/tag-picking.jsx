import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {t, tex} from '#/main/core/translation'

import {TooltipButton} from '#/main/core/layout/button/components/tooltip-button.jsx'
import {HelpBlock} from '#/main/core/layout/form/components/help-block.jsx'
import {Numeric} from '#/main/core/layout/form/components/field/numeric.jsx'
import {FormGroup} from '#/main/core/layout/form/components/group/form-group.jsx'
import {NumberGroup} from '#/main/core/layout/form/components/group/number-group.jsx'
import {RadiosGroup} from '#/main/core/layout/form/components/group/radios-group.jsx'

import {
  shuffleModes,
  SHUFFLE_ALWAYS,
  SHUFFLE_ONCE,
  SHUFFLE_NEVER
} from './../../enums'

// todo : huge c/c from IP list control. Find a way to merge in a generic "item-control/item-list-control" or something like that
const Tag = props =>
  <div className="tag-control">
    <select
      id={props.id}
      className="form-control input-sm"
      value={props.value[0]}
      onChange={e => props.onChange([e.target.value, props.value[1]])}
    >
      <option value="">{tex('quiz_select_picking_tags')}</option>
      {props.tags.map(tag =>
        <option key={tag} value={tag}>{tag}</option>
      )}
    </select>

    <Numeric
      id={`${props.id}-count`}
      className="input-sm"
      min={1}
      value={props.value[1]}
      onChange={value => props.onChange([props.value[0], value])}
    />
  </div>

Tag.propTypes = {
  id: T.string.isRequired,
  tags: T.arrayOf(T.string),
  value: T.array.isRequired,
  onChange: T.func.isRequired
}

class TagList extends Component {
  constructor(props) {
    super(props)

    this.state = {
      pending: ['', 1] // first: tag name / second: nb of questions
    }

    this.addTag        = this.addTag.bind(this)
    this.updateTag     = this.updateTag.bind(this)
    this.updatePending = this.updatePending.bind(this)
    this.removeTag     = this.removeTag.bind(this)
    this.removeAll     = this.removeAll.bind(this)
  }

  addTag() {
    const newTags = this.props.selected.slice()

    newTags.push(this.state.pending)

    this.updatePending(['', 1])

    this.props.onChange(newTags)
  }

  updatePending(newTag) {
    this.setState({
      pending: newTag
    })
  }

  updateTag(index, newTag) {
    const newTags = this.props.selected.slice()

    // update
    newTags[index] = newTag

    this.props.onChange(newTags)
  }

  removeTag(index) {
    const newTags = this.props.selected.slice()

    // remove
    newTags.splice(index, 1)

    this.props.onChange(newTags)
  }

  removeAll() {
    this.props.onChange([])
  }

  render() {
    return (
      <div id={this.props.id} className="tag-list-control">
        <div className="tag-item tag-add">
          <Tag
            id={`${this.props.id}-add`}
            tags={this.props.tags}
            value={this.state.pending}
            onChange={this.updatePending}
          />

          <TooltipButton
            id={`${this.props.id}-add-btn`}
            title={t('add')}
            className="btn-link"
            disabled={!this.state.pending[0] || !this.state.pending[1]}
            onClick={this.addTag}
          >
            <span className="fa fa-fw fa-plus" />
          </TooltipButton>
        </div>

        <HelpBlock help={tex('picking_tag_input_help')} />

        {0 !== this.props.selected.length &&
          <button
            type="button"
            className="btn btn-sm btn-link-danger"
            onClick={this.removeAll}
          >
            {t('delete_all')}
          </button>
        }

        {0 !== this.props.selected.length &&
          <ul>
            {this.props.selected.map((tag, index) =>
              <li key={`${this.props.id}-${index}`} className="tag-item">
                <Tag
                  id={`${this.props.id}-auth-${index}`}
                  tags={this.props.tags}
                  value={tag}
                  onChange={tag => this.updateTag(index, tag)}
                />

                <TooltipButton
                  id={`${this.props.id}-auth-${index}-delete`}
                  title={t('delete')}
                  className="btn-link-danger"
                  onClick={() => this.removeTag(index)}
                >
                  <span className="fa fa-fw fa-trash-o" />
                </TooltipButton>
              </li>
            )}
          </ul>
        }

        {0 === this.props.selected.length &&
          <div className="no-tag-info">{tex('no_picked_tag')}</div>
        }
      </div>
    )
  }
}

TagList.propTypes = {
  id: T.string.isRequired,
  tags: T.arrayOf(T.string).isRequired,
  selected: T.array.isRequired,
  onChange: T.func.isRequired,
  emptyText: T.string
}

const TagPicking = props =>
  <div className="sub-fields">
    <RadiosGroup
      id="quiz-random-pick"
      label={tex('random_picking')}
      options={shuffleModes.filter(m => SHUFFLE_NEVER !== m.value)}
      value={props.randomPick}
      onChange={mode => props.onChange('randomPick', mode)}
      warnOnly={!props.validating}
      error={get(props, 'errors.randomPick')}
    />

    <div className="sub-fields">
      <NumberGroup
        id="quiz-pageSize"
        label={tex('number_question_page')}
        min={1}
        value={props.pageSize}
        onChange={value => props.onChange('pageSize', value)}
        warnOnly={!props.validating}
        error={get(props, 'errors.pageSize')}
      />

      <FormGroup
        id="tag-picking"
        label={tex('tags_to_pick')}
        warnOnly={!props.validating}
        error={get(props, 'errors.pick')}
      >
        <TagList
          id="tag-picking"
          tags={props.tags}
          selected={props.pick}
          onChange={tags => props.onChange('pick', tags)}
        />
      </FormGroup>
    </div>

    <RadiosGroup
      id="quiz-random-order"
      label={tex('random_order')}
      options={SHUFFLE_ALWAYS !== props.randomPick ? shuffleModes : shuffleModes.filter(m => SHUFFLE_ONCE !== m.value)}
      value={props.randomOrder}
      onChange={mode => props.onChange('randomOrder', mode)}
      warnOnly={!props.validating}
      error={get(props, 'errors.randomOrder')}
    />
  </div>

TagPicking.propTypes = {
  tags: T.array.isRequired,
  pick: T.array.isRequired,
  randomPick: T.string.isRequired,
  randomOrder: T.string.isRequired,
  pageSize: T.number,
  validating: T.bool.isRequired,
  errors: T.object,
  onChange: T.func.isRequired
}

export {
  TagPicking
}
