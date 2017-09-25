import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {t, tex} from '#/main/core/translation'
import {HINT_ADD, HINT_CHANGE, HINT_REMOVE} from './../actions'
import {FormGroup} from '#/main/core/layout/form/components/group/form-group.jsx'
import {HtmlGroup} from '#/main/core/layout/form/components/group/html-group.jsx'
import {TextGroup} from '#/main/core/layout/form/components/group/text-group.jsx'
import {Textarea} from '#/main/core/layout/form/components/field/textarea.jsx'
import {ToggleableSet} from '#/main/core/layout/form/components/fieldset/toggleable-set.jsx'
import {TooltipButton} from '#/main/core/layout/button/components/tooltip-button.jsx'
import ObjectsEditor from './item-objects-editor.jsx'
import TagsEditor from '#/plugin/tag/item-tags-editor.jsx'
import {CheckGroup} from '#/main/core/layout/form/components/group/check-group.jsx'

// TODO: add categories, define-as-model

const Metadata = props =>
  <div>
    <TextGroup
      controlId={`item-${props.item.id}-title`}
      label={t('title')}
      value={props.item.title || ''}
      onChange={text => props.onChange('title', text)}
    />

    <HtmlGroup
      controlId={`item-${props.item.id}-description`}
      label={t('description')}
      content={props.item.description || ''}
      onChange={text => props.onChange('description', text)}
    />

    {props.item.rights.edit &&
      <CheckGroup
        checkId={`item-${props.item.id}-editable`}
        label={tex('protect_update')}
        checked={props.item.meta.protectQuestion}
        onChange={checked => props.onChange('meta.protectQuestion', checked)}
      />
    }

    <CheckGroup
      checkId={`item-${props.item.id}-mandatory`}
      label={props.mandatoryQuestions ? tex('make_optional'): tex('mandatory_answer')}
      checked={props.item.meta.mandatory}
      onChange={checked => props.onChange('meta.mandatory', checked)}
    />

    <FormGroup
      controlId={`item-${props.item.id}-objects`}
      label={tex('question_objects')}
    >
      <ObjectsEditor
        showModal={props.showModal}
        closeModal={props.closeModal}
        validating={props.validating}
        item={props.item}
      />
    </FormGroup>
    <FormGroup
      controlId={`item-${props.item.id}-tags`}
      label={t('tags')}
    >
      <TagsEditor
        item={props.item}
      />
    </FormGroup>
  </div>

Metadata.propTypes = {
  item: T.shape({
    id: T.string.isRequired,
    title: T.string.isRequired,
    description: T.string.isRequired,
    rights: T.object.isRequired,
    meta: T.shape({
      mandatory: T.bool.isRequired,
      protectQuestion: T.bool.isRequired
    }).isRequired
  }).isRequired,
  mandatoryQuestions: T.bool.isRequired,
  showModal: T.func.isRequired,
  closeModal: T.func.isRequired,
  onChange: T.func.isRequired,
  validating: T.bool.isRequired
}

const Hint = props =>
  <div className="hint-item">
    <div className="hint-value">
      <Textarea
        id={`hint-${props.id}`}
        title={tex('hint')}
        content={props.value}
        onChange={value => props.onChange(HINT_CHANGE, {id: props.id, value})}
      />
    </div>

    <input
      id={`hint-${props.id}-penalty`}
      title={tex('penalty')}
      type="number"
      min="0"
      value={props.penalty}
      className="form-control hint-penalty"
      aria-label={tex('penalty')}
      onChange={e => props.onChange(
        HINT_CHANGE,
        {id: props.id, penalty: e.target.value}
      )}
    />

    <TooltipButton
      id={`hint-${props.id}-delete`}
      title={t('delete')}
      className="btn-link-default"
      onClick={props.onRemove}
    >
      <span className="fa fa-fw fa-trash-o" />
    </TooltipButton>
  </div>

Hint.propTypes = {
  id: T.string.isRequired,
  value: T.string.isRequired,
  penalty: T.number.isRequired,
  onChange: T.func.isRequired,
  onRemove: T.func.isRequired
}

const Hints = props =>
  <div className="hint-items">
    <label className="control-label" htmlFor="hint-list">
      {tex('hints')}
    </label>

    {props.hints.length === 0 &&
      <div className="no-hint-info">{tex('no_hint_info')}</div>
    }

    {props.hints.length !== 0 &&
      <ul id="hint-list">
        {props.hints.map(hint =>
          <li key={hint.id}>
            <Hint
              {...hint}
              onChange={props.onChange}
              onRemove={() => props.onChange(HINT_REMOVE, {id: hint.id})}
            />
          </li>
        )}
      </ul>
    }

    <button
      type="button"
      className="btn btn-block btn-default"
      onClick={() => props.onChange(HINT_ADD, {})}
    >
      <span className="fa fa-fw fa-plus"/>
      {tex('add_hint')}
    </button>
  </div>

Hints.propTypes = {
  hints: T.arrayOf(T.shape({
    id: T.string.isRequired
  })).isRequired,
  onChange: T.func.isRequired
}

const ItemForm = props =>
  <form>
    <HtmlGroup
      controlId={`item-${props.item.id}-content`}
      label={tex('question')}
      content={props.item.content}
      onChange={content => props.onChange('content', content)}
      warnOnly={!props.validating}
      error={get(props.item, '_errors.content')}
    />

    <ToggleableSet
      showText={tex('show_metadata_fields')}
      hideText={tex('hide_metadata_fields')}
    >
      <Metadata
        mandatoryQuestions={props.mandatoryQuestions}
        item={props.item}
        showModal={props.showModal}
        closeModal={props.closeModal}
        onChange={props.onChange}
        validating={props.validating}
      />
    </ToggleableSet>

    <hr className="item-content-separator" />

    {props.children}

    <hr className="item-content-separator" />

    <ToggleableSet
      showText={tex('show_interact_fields')}
      hideText={tex('hide_interact_fields')}
    >
      <Hints
        hints={props.item.hints}
        onChange={props.onHintsChange}
      />

      <hr className="item-content-separator" />

      <FormGroup
        controlId={`item-${props.item.id}-feedback`}
        label={tex('feedback')}
      >
        <Textarea
          id={`item-${props.item.id}-feedback`}
          content={props.item.feedback}
          onChange={text => props.onChange('feedback', text)}
        />
      </FormGroup>
    </ToggleableSet>
  </form>

ItemForm.propTypes = {
  item: T.shape({
    id: T.string.isRequired,
    content: T.string.isRequired,
    hints: T.arrayOf(T.object).isRequired,
    feedback: T.string.isRequired,
    _errors: T.object
  }).isRequired,
  mandatoryQuestions: T.bool.isRequired,
  children: T.element.isRequired,
  validating: T.bool.isRequired,
  showModal: T.func.isRequired,
  closeModal: T.func.isRequired,
  onChange: T.func.isRequired,
  onHintsChange: T.func.isRequired
}

export {
  ItemForm
}
