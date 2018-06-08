import React from 'react'
import classes from 'classnames'

import {tex, trans} from '#/main/core/translation'
import {makeId} from '#/main/core/scaffolding/id'
import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'
import {FormGroup as FormGroupWithFieldTypes} from '#/main/core/layout/form/prop-types'
import {FormGroup} from '#/main/core/layout/form/components/group/form-group.jsx'
import {TooltipButton} from '#/main/core/layout/button/components/tooltip-button.jsx'
import {SelectGroup}  from '#/main/core/layout/form/components/group/select-group.jsx'
import {NumberGroup}  from '#/main/core/layout/form/components/group/number-group.jsx'

import {Rule as RuleType} from '#/plugin/exo/data/types/score-rules/prop-types'
import {
  RULE_TYPE_MORE,
  RULE_TYPE_LESS,
  RULE_TYPE_BETWEEN,
  RULE_SOURCE_INCORRECT,
  ruleTypes,
  ruleSources,
  ruleTargetsCorrect,
  ruleTargetsIncorrect
} from '#/plugin/exo/items/choice/constants'

const ScoreRule = props =>
  <li className="score-rule-item">
    <div className="score-rule-container">
      <div className="score-rule-row">
        <span className="score-rule-component">{tex('score_rule_text_1')}</span>
        <SelectGroup
          id={`rule-${props.index}-type`}
          className={classes('score-rule-component', {
            'has-error': props.error && props.error.type && !props.warnOnly,
            'has-warning': props.error && props.error.type && props.warnOnly
          })}
          label="type"
          hideLabel={true}
          choices={ruleTypes}
          disabled={props.disabled}
          value={props.rule.type}
          onChange={value => props.onChange('type', value)}
        />
        {-1 < [RULE_TYPE_MORE, RULE_TYPE_LESS].indexOf(props.rule.type) &&
          <NumberGroup
            id={`rule-${props.index}-count`}
            className={classes('score-rule-component score-rule-number-component', {
              'has-error': props.error && props.error.count && !props.warnOnly,
              'has-warning': props.error && props.error.count && props.warnOnly
            })}
            label="count"
            hideLabel={true}
            disabled={props.disabled}
            min={0}
            value={props.rule.count}
            onChange={value => props.onChange('count', value)}
          />
        }
        {RULE_TYPE_BETWEEN === props.rule.type &&
          <NumberGroup
            id={`rule-${props.index}-count-min`}
            className={classes('score-rule-component score-rule-number-component', {
              'has-error': props.error && props.error.countMin && !props.warnOnly,
              'has-warning': props.error && props.error.countMin && props.warnOnly
            })}
            label="count_min"
            hideLabel={true}
            disabled={props.disabled}
            min={0}
            value={props.rule.countMin}
            onChange={value => props.onChange('countMin', value)}
          />
        }
        {RULE_TYPE_BETWEEN === props.rule.type &&
          <span className="score-rule-component">{tex('score_rule_and')}</span>
        }
        {RULE_TYPE_BETWEEN === props.rule.type &&
          <NumberGroup
            id={`rule-${props.index}-count-max`}
            className={classes('score-rule-component score-rule-number-component', {
              'has-error': props.error && props.error.countMax && !props.warnOnly,
              'has-warning': props.error && props.error.countMax && props.warnOnly
            })}
            label="count_max"
            hideLabel={true}
            disabled={props.disabled}
            min={0}
            value={props.rule.countMax}
            onChange={value => props.onChange('countMax', value)}
          />
        }
        <SelectGroup
          id={`rule-${props.index}-source`}
          className={classes('score-rule-component', {
            'has-error': props.error && props.error.source && !props.warnOnly,
            'has-warning': props.error && props.error.source && props.warnOnly
          })}
          label="source"
          hideLabel={true}
          choices={ruleSources}
          disabled={props.disabled}
          value={props.rule.source}
          onChange={value => props.onChange('source', value)}
        />
        <span>,</span>
      </div>

      <div className="score-rule-row">
        <span className="score-rule-component">{tex('score_rule_text_2')}</span>
        <NumberGroup
          id={`rule-${props.index}-points`}
          className={classes('score-rule-component score-rule-number-component', {
            'has-error': props.error && props.error.points && !props.warnOnly,
            'has-warning': props.error && props.error.points && props.warnOnly
          })}
          label="points"
          hideLabel={true}
          disabled={props.disabled}
          value={props.rule.points}
          onChange={value => props.onChange('points', value)}
        />
        <span className="score-rule-component">{tex('score_rule_points')}</span>
        <SelectGroup
          id={`rule-${props.index}-target`}
          className={classes('score-rule-component', {
            'has-error': props.error && props.error.target && !props.warnOnly,
            'has-warning': props.error && props.error.target && props.warnOnly
          })}
          label="target"
          hideLabel={true}
          choices={props.rule.source === RULE_SOURCE_INCORRECT ? ruleTargetsIncorrect : ruleTargetsCorrect}
          disabled={props.disabled}
          value={props.rule.target}
          onChange={value => props.onChange('target', value)}
        />
      </div>
    </div>

    <div className="right-controls">
      <TooltipButton
        id={`rule-${props.rule.id}-delete`}
        className="btn-link-danger"
        title={trans('delete')}
        onClick={props.onDelete}
      >
        <span className="fa fa-fw fa-trash-o" />
      </TooltipButton>
    </div>
  </li>

ScoreRule.propTypes = {
  index: T.number.isRequired,
  rule: T.shape(
    RuleType.propTypes
  ).isRequired,
  error: T.object,
  validating: T.bool,
  warnOnly: T.bool,
  disabled: T.bool,
  onChange: T.func.isRequired,
  onDelete: T.func.isRequired
}

const ScoreRulesGroup = props =>
  <FormGroup
    {...props}
    error={props.error && typeof props.error === 'string' ? props.error : undefined}
    className="score-rules-group"
  >
    <div className="well well-sm">
      <div>{tex('score_rule_considered')} <b>{tex('score_rule_correct_answers')}</b> :</div>
      <ul className="score-rules-info-list">
        <li>{tex('selected_correct_choices')}</li>
        <li>{tex('unselected_incorrect_choices')}</li>
      </ul>
      <div>{tex('score_rule_considered')} <b>{tex('score_rule_incorrect_answers')}</b> :</div>
      <ul className="score-rules-info-list">
        <li>{tex('selected_incorrect_choices')}</li>
        <li>{tex('unselected_correct_choices')}</li>
      </ul>
      <div>{tex('score_rules_conflict_warning')}</div>
    </div>

    {0!== props.value.length &&
      <ul>
        {props.value.map((rule, index) =>
          <ScoreRule
            key={`score-rule-${index}`}
            index={index}
            rule={rule}
            validating={props.validating}
            warnOnly={props.warnOnly}
            disabled={props.disabled}
            error={props.error && typeof props.error !== 'string' ? props.error[index] : undefined}
            onChange={(propName, propValue) => {
              const newRule = Object.assign({}, rule, {
                [propName]: propValue
              })

              const newScoreRules = props.value.slice()
              newScoreRules.splice(index, 1, newRule)

              props.onChange(newScoreRules)
            }}
            onDelete={() => {
              const newScoreRules = props.value.slice()
              newScoreRules.splice(index, 1)

              props.onChange(newScoreRules)
            }}
          />
        )}
      </ul>
    }

    {0 === props.value.length &&
      <div className="no-rule-info">{props.placeholder}</div>
    }

    <button
      className="btn btn-block btn-default"
      type="button"
      onClick={() => props.onChange([].concat(props.value, [{
        id: makeId(),
        type: '',
        source: '',
        count: 1,
        countMin: 1,
        countMax: 1,
        points: 0,
        target: ''
      }]))}
    >
      <span className="fa fa-fw fa-plus icon-with-text-right" />
      {tex('add_rule')}
    </button>
  </FormGroup>

implementPropTypes(ScoreRulesGroup, FormGroupWithFieldTypes, {
  // more precise value type
  value: T.arrayOf(
    T.shape(RuleType.propTypes)
  ),
  // override error types to handles individual criterion errors
  error: T.oneOfType([T.string, T.object])
}, {
  value: [],
  placeholder: tex('no_rule')
})

export {
  ScoreRulesGroup
}
