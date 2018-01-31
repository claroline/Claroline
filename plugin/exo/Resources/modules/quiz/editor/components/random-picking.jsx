import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {tex} from '#/main/core/translation'

import {NumberGroup} from '#/main/core/layout/form/components/group/number-group.jsx'
import {RadiosGroup} from '#/main/core/layout/form/components/group/radios-group.jsx'

import {
  shuffleModes,
  SHUFFLE_ALWAYS,
  SHUFFLE_ONCE,
  SHUFFLE_NEVER
} from './../../enums'

const RandomPicking = props =>
  <div className="sub-fields">
    <RadiosGroup
      id="quiz-random-pick"
      label={tex('random_picking')}
      options={shuffleModes}
      value={props.randomPick}
      onChange={mode => props.onChange('randomPick', mode)}
      warnOnly={!props.validating}
      error={get(props, 'errors.randomPick')}
    />

    {props.randomPick !== SHUFFLE_NEVER &&
      <div className="sub-fields">
        <NumberGroup
          id="quiz-pick"
          label={tex('number_steps_draw')}
          min={0}
          value={props.pick}
          onChange={value => props.onChange('pick', value)}
          help={tex('number_steps_draw_help')}
          warnOnly={!props.validating}
          error={get(props, 'errors.pick')}
        />
      </div>
    }

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

RandomPicking.propTypes = {
  pick: T.number.isRequired,
  randomPick: T.string.isRequired,
  randomOrder: T.string.isRequired,
  validating: T.bool.isRequired,
  errors: T.object,
  onChange: T.func.isRequired
}

export {
  RandomPicking
}
