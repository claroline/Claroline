import React from 'react'
import classes from 'classnames'

import {trans} from '#/main/app/intl/translation'
import {makeId} from '#/main/core/scaffolding/id'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {SelectGroup}  from '#/main/core/layout/form/components/group/select-group'
import {NumberGroup}  from '#/main/core/layout/form/components/group/number-group'
import {AlertBlock} from '#/main/app/alert/components/alert-block'

import {Rule as RuleType} from '#/plugin/exo/data/types/score-rules/prop-types'
import {constants} from '#/plugin/exo/scores/rules/constants'

const ScoreRule = props =>
  <li className="score-rule-item">
    <div className="score-rule-container">
      <div className="score-rule-row">
        <span className="score-rule-component">{trans('score_rule_text_1', {}, 'quiz')}</span>

        <SelectGroup
          id={`rule-${props.index}-type`}
          className={classes('score-rule-component', {
            'has-error': props.error && props.error.type && !props.warnOnly,
            'has-warning': props.error && props.error.type && props.warnOnly
          })}
          label="type"
          hideLabel={true}
          choices={constants.RULE_TYPES}
          disabled={props.disabled}
          value={props.rule.type}
          onChange={value => props.onChange('type', value)}
          size="sm"
        />

        {-1 < [constants.RULE_TYPE_MORE, constants.RULE_TYPE_LESS].indexOf(props.rule.type) &&
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
            size="sm"
          />
        }

        {constants.RULE_TYPE_BETWEEN === props.rule.type &&
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
            size="sm"
          />
        }

        {constants.RULE_TYPE_BETWEEN === props.rule.type &&
          <span className="score-rule-component">{trans('score_rule_and', {}, 'quiz')}</span>
        }

        {constants.RULE_TYPE_BETWEEN === props.rule.type &&
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
            size="sm"
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
          choices={constants.RULE_SOURCES}
          disabled={props.disabled}
          value={props.rule.source}
          onChange={value => props.onChange('source', value)}
          size="sm"
        />
        <span>,</span>
      </div>

      <div className="score-rule-row">
        <span className="score-rule-component">{trans('score_rule_text_2', {}, 'quiz')}</span>
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
          size="sm"
        />
        <span className="score-rule-component">{trans('score_rule_points', {}, 'quiz')}</span>
        <SelectGroup
          id={`rule-${props.index}-target`}
          className={classes('score-rule-component', {
            'has-error': props.error && props.error.target && !props.warnOnly,
            'has-warning': props.error && props.error.target && props.warnOnly
          })}
          label="target"
          hideLabel={true}
          choices={props.rule.source === constants.RULE_SOURCE_INCORRECT ? constants.RULE_TARGETS_INCORRECT : constants.RULE_TARGETS_CORRECT}
          disabled={props.disabled}
          value={props.rule.target}
          onChange={value => props.onChange('target', value)}
          size="sm"
        />
      </div>
    </div>

    <div className="right-controls">
      <Button
        id={`rule-${props.rule.id}-delete`}
        className="btn-link"
        type={CALLBACK_BUTTON}
        icon="fa fa-fw fa-trash"
        label={trans('delete', {}, 'actions')}
        callback={props.onDelete}
        tooltip="top"
        dangerous={true}
      />
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

const ScoreRulesInput = props =>
  <div className="score-rules-group">
    <AlertBlock
      type="info"
    >
      <div>{trans('score_rule_considered', {}, 'quiz')} <b>{trans('score_rule_correct_answers', {}, 'quiz')}</b> :</div>
      <ul className="score-rules-info-list">
        <li>{trans('selected_correct_choices', {}, 'quiz')}</li>
        <li>{trans('unselected_incorrect_choices', {}, 'quiz')}</li>
      </ul>
      <div>{trans('score_rule_considered', {}, 'quiz')} <b>{trans('score_rule_incorrect_answers', {}, 'quiz')}</b> :</div>
      <ul className="score-rules-info-list">
        <li>{trans('selected_incorrect_choices', {}, 'quiz')}</li>
        <li>{trans('unselected_correct_choices', {}, 'quiz')}</li>
      </ul>
      <div>{trans('score_rules_conflict_warning', {}, 'quiz')}</div>
    </AlertBlock>

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
      <div className="no-item-info">{props.placeholder}</div>
    }

    <Button
      className="btn btn-block "
      type={CALLBACK_BUTTON}
      icon="fa fa-fw fa-plus"
      label={trans('add_rule', {}, 'quiz')}
      callback={() => props.onChange([].concat(props.value, [{
        id: makeId(),
        type: '',
        source: '',
        count: 1,
        countMin: 1,
        countMax: 1,
        points: 0,
        target: ''
      }]))}
    />
  </div>

implementPropTypes(ScoreRulesInput, DataInputTypes, {
  // more precise value type
  value: T.arrayOf(
    T.shape(RuleType.propTypes)
  ),
  // override error types to handles individual criterion errors
  error: T.oneOfType([T.string, T.object])
}, {
  value: [],
  placeholder: trans('no_rule', {}, 'quiz')
})

export {
  ScoreRulesInput
}
