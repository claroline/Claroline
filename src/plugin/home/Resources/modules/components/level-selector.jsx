import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {TooltipOverlay} from '#/main/app/overlays/tooltip/components/overlay'

const LevelSelector = (props) =>
  <TooltipOverlay id={`${props.id}-tip`} tip={props.label} position="bottom">
    <div className={classes('btn-group rounded-1', props.className)} role="group" aria-label={props.label}>
      {[1, 2, 3, 4, 5, 6].map(level =>
        <>
          <input
            type="radio"
            className="btn-check btn-sm"
            name="btnradio"
            id={`${props.id}-${level}`}
            autoComplete="off"
            checked={props.value === level}
            onChange={() => props.onChange(level)}
          />
          <label
            className={classes('btn btn-sm btn-outline-primary', 1 === level && 'rounded-start-1', 6 === level && 'rounded-end-1')}
            htmlFor={`${props.id}-${level}`}
          >
            {level}
          </label>
        </>
      )}
    </div>
  </TooltipOverlay>

LevelSelector.propTypes = {
  className: T.string,
  id: T.string.isRequired,
  label: T.string.isRequired,
  value: T.number,
  onChange: T.func.isRequired
}

export {
  LevelSelector
}
