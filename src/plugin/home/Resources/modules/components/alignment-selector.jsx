import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {TooltipOverlay} from '#/main/app/overlays/tooltip/components/overlay'

const AlignmentChoice = (props) =>
  <>
    <input type="radio" className="btn-check btn-sm" name={props.id} id={props.id} autoComplete="off" checked={props.selected} onChange={props.onChange}/>
    <label className={classes('btn btn-sm btn-outline-primary', props.className)} htmlFor={props.id}>
      <span className={`fa fa-fw fa-${props.icon}`}></span>
    </label>
  </>

AlignmentChoice. propTypes = {
  id: T.string.isRequired,
  className: T.string,
  selected: T.bool.isRequired,
  icon: T.string.isRequired,
  onChange: T.func.isRequired
}

const AlignmentSelector = (props) =>
  <TooltipOverlay id={`${props.id}-tip`} tip={props.label} position="bottom">
    <div className="btn-group rounded-1" role="group" aria-label={props.label}>
      <AlignmentChoice
        className="rounded-start-1"
        id={`${props.id}-start`}
        selected={'left' === props.value}
        icon="align-left"
        onChange={() => props.onChange('left')}
      />

      <AlignmentChoice
        id={`${props.id}-center`}
        selected={'center' === props.value}
        icon="align-center"
        onChange={() => props.onChange('center')}
      />

      <AlignmentChoice
        className="rounded-end-1"
        id={`${props.id}-right`}
        selected={'right' === props.value}
        icon="align-right"
        onChange={() => props.onChange('right')}
      />
    </div>
  </TooltipOverlay>

AlignmentSelector.propTypes = {
  id: T.string.isRequired,
  label: T.string.isRequired,
  value: T.oneOf(['left', 'center', 'right']),
  onChange: T.func.isRequired
}

export {
  AlignmentSelector
}
